<?php

namespace ShelfDB {

  class Picture {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    private function GetImageFolderFromType($type) {
      switch( strtoupper($type) ) {
        case 'T':
          $imgDir = '/img/thumb';
          break;
        case 'P':
          $imgDir = '/img/parts';
          break;
        case 'F':
          $imgDir = '/img/footprint';
          break;
        case 'SU':
          $imgDir = '/img/supplier';
          break;
        default:
          return false;
      }

      return $imgDir;
    }

    public function GetByFilename($fileName, $elementType) {
      $fileName = $this->db()->sql->real_escape_string($fileName);
      $query = "SELECT id FROM pictures WHERE pict_type = '$elementType' AND pict_fname = '$fileName';";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      if( !$res ) return null;

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      foreach( $data as &$img ) {
        $img = $this->GetById($img['id']);
      }

      return $data;
    }

    public function SetPartMasterById(int $id) {

      $picInfo = $this->GetById($id);

      if( !$picInfo ) return null;

      // Get list of group images
      $query = "SELECT * FROM pictures WHERE NOT id = $id AND pict_type = 'P' AND parent_id = ".$picInfo['parent_id'].";";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res ) return null;

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      // Set master
      if( $picInfo['pict_masterpict'] != '1' ) {
        $query = "UPDATE pictures SET pict_masterpict = 1 WHERE id = $id;";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
        if( !$res ) return null;

        $this->db()->History()->Add($id, 'PIC', 'edit', 'pict_masterpict', 0, 1 );
      }

      // Finished
      if( sizeof($data) == 0 ) return true;

      // Remove master from all other pictures
      $query = "UPDATE pictures SET pict_masterpict = 0 WHERE id IN (".join(',', array_map(function($x){ return $x['id']; },$data) ).");";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      foreach( $data as $pic ) {
        if( $pic['pict_masterpict'] != '0' )
          $this->db()->History()->Add($pic['id'], 'PIC', 'edit', 'pict_masterpict', 1, 0 );
      }

      return $res;
    }

    public function GetById(int $id) {

      $query = "SELECT p.*, t.* FROM pictures p "
        ."LEFT JOIN "
          ."("
            ."SELECT i.tn_pictid, GROUP_CONCAT(i.id) AS tn_id_arr, GROUP_CONCAT(i.pict_fname SEPARATOR '/') AS tn_fname_arr FROM pictures i "
            ."WHERE i.pict_type = 'T' GROUP BY i.tn_pictid"
          .") t ON t.tn_pictid = p.id WHERE p.id = $id";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
      {
        return null;
      }

      $data = $res->fetch_assoc();
      $res->free();

      // Do some Cleanup
      $tn_ids    = array_filter(explode(',', $data['tn_id_arr']));
      $tn_fnames = array_filter(explode('/', $data['tn_fname_arr']));
      for($i = 0; $i < sizeof($tn_ids) ; $i++ ) {
        $data['thumbnails'][] = array('id' => $tn_ids[$i], 'filename' => $tn_fnames[$i]);
      }

      return $data;
    }

    public function GetByElementId(int $id, $elementType) {

      $elementType = strtoupper($elementType);

      switch( $elementType ) {
        case 'P':
        case 'F':
        case 'SU':
          break;
        default:
          return null;
      }

      $query = "SELECT p.*, t.* FROM pictures p "
        ."LEFT JOIN "
          ."("
            ."SELECT i.tn_pictid, GROUP_CONCAT(i.id) AS tn_id_arr, GROUP_CONCAT(i.pict_fname SEPARATOR '/') AS tn_fname_arr FROM pictures i "
            ."WHERE i.pict_type = 'T' GROUP BY i.tn_pictid"
          .") t ON t.tn_pictid = p.id WHERE p.parent_id = $id AND p.pict_type = '$elementType';";
          \Log::Info($query);
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
      {
        return null;
      }

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      // Do some Cleanup
      foreach( $data as &$entry ) {
        $tn_ids    = array_filter(explode(',', $entry['tn_id_arr']));
        $tn_fnames = array_filter(explode('/', $entry['tn_fname_arr']));
        for($i = 0; $i < sizeof($tn_ids) ; $i++ ) {
          $entry['thumbnails'][] = array('id' => $tn_ids[$i], 'filename' => $tn_fnames[$i]);
        }
      }

      return $data;
    }

    public function DeleteAllByElementId(int $id, $elementType) {
      $images = $this->GetByElementId($id, $elementType);
      \Log::Info("Deleting all images for element (id = $id, type = $elementType)");
      foreach( $images as $image ) {
        $this->DeleteById($image['id']);
      }
    }

    public function DeleteById(int $id) {
      // First get the image (and its thumbnails)
      $img = $this->GetById($id);
      if( !$img )
        return false;

      // Get folder from type
      $imgDir = $this->GetImageFolderFromType($img['pict_type']);
      if( !$imgDir ) return false;

      $removeImageFun = function($fullPath, $id, $img) {
        // Delete from DB
        $query = "DELETE FROM pictures WHERE id = $id;";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
        if( $res ) {
          // Delete from FS
          if( $fullPath != "" && file_exists( $fullPath ) ) {
            unlink( $fullPath );
            \Log::Info("Deleted image id = $id at path \"$fullPath\"");
          } else {
            \Log::Info("Deleted image id = $id but kept the image file");
          }

          // History
          $this->db()->History()->Add($id, 'PIC', 'delete', 'object', $img ,'');

          return true;
        } else {
          \Log::Error("Deleting of image id = $id at path \"$fullPath\" failed!");
          return false;
        }
      };

      // Check if there are any other instances used of the image file - if not delete!
      $imgInstances = $this->GetByFilename($img['pict_fname'], $img['pict_type']);
      $deleteFiles  = !$imgInstances || ( sizeof($imgInstances) <= 1 );

      $fullImagePath = joinPaths($this->db()->AbsRoot(), $imgDir, $img['pict_fname']);

      $removeImageFun( $deleteFiles ? $fullImagePath : "", $img['id'], $img );

      for( $i = 0; $i < sizeof( $img['thumbnails']); $i++ ) {
        $fullImagePath = joinPaths($this->db()->AbsRoot(), '/img/thumb', $img['thumbnails'][$i]['filename']);
        $removeImageFun( $deleteFiles ? $fullImagePath : "", $img['thumbnails'][$i]['id'], $img['thumbnails'][$i] );
      }

      return true;
    }

    public function CreateCopyFromId(int $picId, int $parentId) {
        $query = "CREATE TEMPORARY TABLE tempTable AS SELECT * FROM pictures WHERE id=$picId;
                  UPDATE tempTable SET parent_id=$parentId, id=0;
                  INSERT INTO pictures SELECT * FROM tempTable;";
        $res = $this->db()->sql->multi_query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

        // TODO create thumbnails
        //
        if( $res ) {
          do {
            if( $res = $this->db()->sql->store_result() ) {
              $res->fetch_all(MYSQLI_ASSOC);
              $res->free();
            }
          } while($this->db()->sql->more_results() && $this->db()->sql->next_result() );
          $newid = $this->db()->sql->insert_id;
          \Log::Info("Created new picture entry (id = $newid, parent_id = $parentId) as copy of id $picId");

          return $newid;
        }
        return false;
    }

    public function Create(int $elementId, $elementType /* 'F' footprint, 'P' part*/,
      $fileName, $deletePrevious = false ) {

        $elementType = strtoupper($elementType);

        // TODO get image dimensions and create thumbnails
        $path = $this->GetImageFolderFromType($elementType);
        if( !$path ) return false;

        if( in_array( $elementType, array( 'F', 'SU' ) ) )
          $deletePrevious = true;

        // Check if the file exists
        if( file_exists( joinPaths($this->db()->AbsRoot(), $path, $fileName) ) ) {
          // Create entry
          $fn = $this->db()->sql->real_escape_string($fileName);
          $query = "INSERT INTO pictures (parent_id, pict_fname, pict_width, pict_height, pict_type) VALUES ($elementId, '$fn', 0, 0, '$elementType');";
          $res = $this->db()->sql->query($query);

          // TODO create thumbnails
          //
          if( $res === true ) {
            $newid = $this->db()->sql->insert_id;
            \Log::Info("Created new picture entry (id = $newid, type = $elementType, fileName = \"$fn\")");

            // Delete old picture and its thumbnails
            if( $deletePrevious ) {
              \Log::Info("Deleting previously linked images for element (id = $elementId, type = $elementType)");
              $images = $this->GetByElementId($elementId, $elementType);
              foreach( $images as $image ) {
                if( $image['id'] != $newid )
                  $this->DeleteById($image['id']);
              }
            }

            return $newid;

          } else {
            \Log::Error("Error creating new picture entry (type = $elementId, fileName = \"$fn\")");
            \Log::WarningSQLQuery($query, $this->db()->sql);
            return false;
          }
        } else {
          return false;
        }
      }
  }
}

?>
