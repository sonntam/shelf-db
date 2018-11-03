<?php

namespace ShelfDB {

  class Datasheet {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    public function GetDatasheetFolder() {
      return "/attachments/datasheets";
    }

    public function GetByFilename($fileName) {
      $fileName = $this->db()->sql->real_escape_string($fileName);
      $query = "SELECT id FROM datasheets WHERE datasheetFileName = '$fileName';";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      if( !$res ) return null;

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      foreach( $data as &$ds ) {
        $ds = $this->GetById($ds['id']);
      }

      return $data;
    }

    public function GetById(int $id) {

      $query = "SELECT * FROM datasheets WHERE id = $id";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
      {
        return null;
      }

      $data = $res->fetch_assoc();
      $res->free();

      return $data;
    }

    public function GetByPartId(int $id) {

      $query = "SELECT * FROM datasheets WHERE part_id = $id;";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
      {
        return null;
      }

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      return $data;
    }

    public function DeleteAllByPartId(int $id) {
      $datasheets = $this->GetByPartId($id);
      \Log::Info("Deleting all images for part (id = $id)");
      foreach( $datasheets as $ds ) {
        $this->DeleteById($ds['id']);
      }
    }

    public function DeleteById(int $id) {
      // First get the image (and its thumbnails)
      $ds = $this->GetById($id);
      if( !$ds )
        return false;

      // Get folder from type
      $dsDir = $this->GetDatasheetFolder();

      $removeFileFun = function($fullPath, $id, $ds) {
        // Delete from DB
        $query = "DELETE FROM datasheets WHERE id = $id;";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
        if( $res ) {
          // Delete from FS
          if( $fullPath != "" && file_exists( $fullPath ) ) {
            unlink( $fullPath );
            \Log::Info("Deleted datasheet id = $id at path \"$fullPath\"");
          } else {
            \Log::Info("Deleted datasheet id = $id but kept the image file");
          }

          // History
          $this->db()->History()->Add($id, 'DS', 'delete', 'object', $ds ,'');

          return true;
        } else {
          \Log::Error("Deleting of datasheet id = $id at path \"$fullPath\" failed!");
          return false;
        }
      };

      // Check if there are any other instances used of the image file - if not delete!
      $dsInstances  = $this->GetByFilename($ds['datasheetFileName']);
      $deleteFiles  = !$dsInstances || ( sizeof($dsInstances) <= 1 );

      $fullImagePath = joinPaths($this->db()->AbsRoot(), $dsDir, $ds['datasheetFileName']);

      $removeFileFun( $deleteFiles ? $fullImagePath : "", $ds['id'], $ds );

      return true;
    }

    public function Create(int $elementId, $fileName, $name, int $filesize ) {

        // TODO get image dimensions and create thumbnails
        $path = $this->GetDatasheetFolder();
        if( !$path ) return false;

        // Check if the file exists
        if( file_exists( joinPaths($this->db()->AbsRoot(), $path, $fileName) ) ) {
          // Create entry
          $fn = $this->db()->sql->real_escape_string($fileName);
          $name = $this->db()->sql->real_escape_string($name);
          $query = "INSERT INTO datasheets (part_id, datasheetFileName, name, filesize) VALUES ($elementId, '$fn', '$name', $filesize);";
          $res = $this->db()->sql->query($query);

          // TODO create thumbnails
          //
          if( $res === true ) {
            $newid = $this->db()->sql->insert_id;
            \Log::Info("Created new datasheet entry (id = $newid, fileName = \"$fn\")");

            return $newid;

          } else {
            \Log::Error("Error creating new datasheet entry (id = $elementId, fileName = \"$fn\")");
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
