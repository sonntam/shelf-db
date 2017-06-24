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

      return $data;
    }
  }
} // END NAMESPACE
?>
