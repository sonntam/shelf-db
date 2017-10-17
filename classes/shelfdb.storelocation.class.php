<?php

namespace ShelfDB {

  class StoreLocation {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
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

    public function GetById($id = null) {

      // Extract Part
      if( $id === null ) {
        $query = "SELECT * FROM storeloc ORDER BY udf_NaturalSortFormat(name, 10, \".,\")";
      } else {
        $query = "SELECT * FROM storeloc WHERE id = $id";
      }
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      if( sizeof($data) == 1 ) {
        $data = $data[0];
      }

      return $data;
    }

    public function GetEmpty() {
      $query = "SELECT * FROM storeloc s WHERE NOT id IN (SELECT DISTINCT id_storeloc FROM parts) ORDER BY udf_NaturalSortFormat(name, 10, \".,\")";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        return $data;
      } else {
        return null;
      }
    }

    public function GetNonEmpty() {
      $query = "SELECT s.* FROM storeloc s "
        ."WHERE id IN (SELECT DISTINCT id_storeloc FROM parts) ORDER BY udf_NaturalSortFormat(name, 10, \".,\")";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        return $data;
      } else {
        return null;
      }
    }

    public function DeleteById(int $id) {
      // First try to get it
      $fp = $this->GetById($id);
      if( !$fp ) return false;

      // Delete the footprint and update all parts to use the default footprint id = 0
      if( !($this->db()->Part()->AllReplaceStorelocationId($id, 0)) )
        return false;

      $query = "DELETE FROM storeloc WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      // Update history
      $this->db()->History()->Add($id, 'SL', 'delete', 'object', $fp, '' );

      if( !$res )
        return false; // Database my be inconsistent because footrprints have already been replaced

      return true;
    }

    public function GetByName($name) {
      $name = trim($name);
      if( $name == "" )
        return null;

      $name = $this->db()->sql->real_escape_string($name);
      $query = "SELECT f.id, f.name, FROM storeloc f WHERE f.name = '$name';";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      return $fp;
    }

    public function ExistsByName($name) {
      $name = trim($name);
      if( $name == "" )
        return false;

      $name = $this->db()->sql->real_escape_string($name);
      $query = "SELECT f.name FROM storeloc f WHERE f.name = '$name';";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $result = $res->num_rows > 0;

      $res->free();

      return $result;
    }

    public function Create($name) {
      $name = trim($name);
      $esname = $this->db()->sql->real_escape_string( $name );
      $query = "INSERT INTO `storeloc` (`name`) VALUES ('$esname')";

      $res = $this->db()->sql->query($query);

      if( $res === true ) {
        $newid = $this->db()->sql->insert_id;

        // Update history
        $this->db()->History()->Add($newid, 'SL', 'create', 'object', '', array(
          "id" => $newid,
          "name" => $name
        ) );

        return array('id' => $newid);

      } else {
        \Log::WarningSQLQuery($query, $this->db()->sql);
        return false;
      }
    }

    public function SetNameById($id, $name) {
      $name = trim($name);
      if( $name == "" )
        return;

      $esname = $this->db()->sql->real_escape_string($name);

      $oldname = $this->GetNameById($id);

      $query = "UPDATE storeloc SET name = '$esname' WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        // Update history
        $this->db()->History()->Add($id, 'SL', 'edit', 'name', $oldname, $name);
      }

      return $res;
    }

    public function GetNameById($id) {

      $query = "SELECT name FROM storeloc WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      return $fp['name'];
    }
  }
} // END NAMESPACE
?>
