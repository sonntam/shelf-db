<?php

namespace ShelfDB {

  class StoreLocations {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    public function GetAll() {
      return $this->GetById();
    }

    public function GetById($id = null) {

      // Extract Part
      if( $id === null ) {
        $query = "SELECT * FROM storeloc ORDER BY udf_NaturalSortFormat(name, 10, \".,\")";
      } else {
        $query = "SELECT * FROM storeloc WHERE id = $id";
      }
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      if( sizeof($data) == 1 ) {
        $data = $data[0];
      }

      return $data;
    }

    public function DeleteById(int $id) {
      // First try to get it
      $fp = $this->GetById($id);
      if( !$fp ) return false;

      // Delete the footprint and update all parts to use the default footprint id = 0
      if( !($this->db->Parts()->AllReplaceStorelocationId($id, 0)) )
        return false;

      $query = "DELETE FROM storeloc WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( !$res )
        return false; // Database my be inconsistent because footrprints have already been replaced

      return true;
    }

    public function GetByName($name) {
      $name = trim($name);
      if( $name == "" )
        return null;

      $name = $this->db->sql->real_escape_string($name);
      $query = "SELECT f.id, f.name, FROM storeloc f WHERE f.name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      return $fp;
    }

    public function ExistsByName($name) {
      $name = trim($name);
      if( $name == "" )
        return false;

      $name = $this->db->sql->real_escape_string($name);
      $query = "SELECT f.name FROM storeloc f WHERE f.name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $result = $res->num_rows > 0;

      $res->free();

      return $result;
    }

    public function Create($name) {
      $name = trim($name);
      $name = $this->db->sql->real_escape_string( $name );
      $query = "INSERT INTO `storeloc` (`name`) VALUES ('$name')";

      $res = $this->db->sql->query($query);

      if( $res === true ) {
        $newid = $this->db->sql->insert_id;

        return array('id' => $newid);

      } else {
        \Log::WarningSQLQuery($query, $this->db->sql);
        return false;
      }
    }

    public function SetNameById($id, $name) {
      $name = trim($name);
      if( $name == "" )
        return;

      $name = $this->db->sql->real_escape_string($name);
      $query = "UPDATE storeloc SET name = '$name' WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }

    public function GetNameById($id) {

      $query = "SELECT name FROM storeloc WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      return $fp['name'];
    }
  }
} // END NAMESPACE
?>
