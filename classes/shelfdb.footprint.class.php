<?php

namespace ShelfDB {
  class Footprints {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    public function DeleteById(int $id) {
      // First try to get it
      $fp = $this->GetById($id);
      if( !$fp ) return false;

      // Delete the footprint and update all parts to use the default footprint id = 0
      if( !($this->db->Parts()->AllReplaceFootprintId($id, 0)) )
        return false;

      $query = "DELETE FROM footprints WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( !$res )
        return false; // Database my be inconsistent because footrprints have already been replaced

      // Update history
      $this->db->History()->Add($id, 'P', "delete", 'object', '', $fp );

      // Now delete the image
      if( isset($fp['pict_id']) && $fp['pict_id'] ){
        \Log::Info("Trying to delete the image entry for footprint id = $id");
        $this->db->Pictures()->DeleteById($fp['pict_id']);
      }
      return true;
    }

    public function GetAll() {
      $el = $this->GetById();

      // Check if only one element was returned. If so, build array
      if( $el && !is_array($el[0]) )
      {
        $newel = array($el);
        return $newel;
      }

      return $el;
    }

    public function ExistsByName($name) {
      $name = trim($name);
      if( $name == "" )
        return false;

      $name = $this->db->sql->real_escape_string($name);
      $query = "SELECT f.name FROM footprints f WHERE f.name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $result = $res->num_rows > 0;

      $res->free();

      return $result;
    }

    public function CreateFromId($name, $baseid) {
      $name = trim($name);
      $newId = $this->Create($name, "");
      if( $newId )
      {
        // Create copy of image
        $fp = $this->GetById($baseid);
        if( !$fp ); // error but ignore for now

        if( $fp['pict_id'] ) {
          $newId['picId'] = $this->db->Pictures()->CreateCopyFromId($fp['pict_id'], $newId['id']);
        }

        return $newId;
      }
      return false;
    }

    public function Create($name, $pictureFileName) {
      $name = trim($name);
      $name = $this->db->sql->real_escape_string( $name );
      $query = "INSERT INTO `footprints` (`name`) VALUES ('$name')";

      $res = $this->db->sql->query($query);

      if( $res === true ) {
        $newid = $this->db->sql->insert_id;

        // Create picture
        $picid = null;
        if( $pictureFileName != "" )
          $picid = $this->db->Pictures()->Create($newid, 'F', $pictureFileName, false);

        $fp = array('id' => $newid, 'name' => $name, 'picId' => $picid);

        // History update
        $this->db->History()->Add($newid, 'F', 'create', 'object', '', $fp);

        return $fp;

      } else {
        \Log::WarningSQLQuery($query, $this->db->sql);
        return false;
      }
    }

    public function GetByName($name) {
      $name = trim($name);
      if( $name == "" )
        return null;

      $name = $this->db->sql->real_escape_string($name);
      $query = "SELECT f.id, f.name, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM footprints f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'F' WHERE f.name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      return $fp;
    }

    public function SetNameById($id, $name) {
      $name = trim($name);
      if( $name == "" )
        return;

      $oldname = $this->GetNameById($id);

      if( !$oldname ) return false;

      $esname = $this->db->sql->real_escape_string($name);
      $query = "UPDATE footprints SET name = '$esname' WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      // History
      $this->db->History()->Add($id, 'F', 'edit', 'name', $oldname, $name);

      return $res;
    }

    public function GetNameById($id) {

      $query = "SELECT name FROM footprints WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      return $fp['name'];
    }

    public function GetById($id = null) {

      if( $id === null ) {
        // Get All
        $query = "SELECT f.id, f.name, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM footprints f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'F' ORDER BY udf_NaturalSortFormat(f.name, 10, \".,\")";
      } else {
        $query = "SELECT f.id, f.name, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM footprints f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'F' WHERE f.id = $id ORDER BY udf_NaturalSortFormat(f.name, 10, \".,\") LIMIT 1";
      }

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
      if( !$res ) return false;

      $children = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      if( sizeof($children) == 1 ) {
        $children = $children[0];
      }

      return $children;
    }

  }
} // END NAMESPACE
?>
