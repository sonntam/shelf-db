<?php

namespace ShelfDB {
  class Suppliers {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    public function ExpandRawUrl( $rawurl, $partNr ) {

      $match = array();
      if( preg_match( "/<!PARTNR;?(.*?)!>/", $rawurl, $match) ) {
        // Construct
        $repArgs = explode(';', $match[1]);
        foreach($repArgs as &$arg ) {
          $arg = explode(':',$arg);
          if( sizeof($arg) == 2 )
            $partNr = str_replace($arg[0], $arg[1], $partNr);
        }
        return preg_replace("/<!PARTNR(?:;.*?)?!>/", urlencode($partNr), $rawurl);
      } else {
        return $rawurl;
      }
    }

    public function GetUrlFromId(int $id, $partNr) {
      $query = "SELECT urlTemplate FROM suppliers WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        $data = $res->fetch_assoc();
        $res->free();

        $url = $data['urlTemplate'];
        return $this->ExpandRawUrl($url, $partNr);
      }
      return false;
    }

    public function DeleteById(int $id) {
      // First try to get it
      $su = $this->GetById($id);
      if( !$su ) return false;

      // Delete the supplier and update all parts to use the default supplier id = 0
      if( !($this->db->Parts()->AllReplaceSupplierId($id, 0)) )
        return false;

      $query = "DELETE FROM suppliers WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( !$res )
        return false; // Database my be inconsistent because footrprints have already been replaced

      // Now delete the image
      if( isset($su['pict_id']) && $su['pict_id'] ){
        \Log::Info("Trying to delete the image entry for supplier id = $id");
        $this->db->Pictures()->DeleteById($su['pict_id']);
      }
      return true;
    }

    public function GetAll() {
      return $this->GetById();
    }

    public function ExistsByName($name) {
      $name = trim($name);
      if( $name == "" )
        return false;

      $name = $this->db->sql->real_escape_string($name);
      $query = "SELECT f.name FROM suppliers f WHERE f.name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $result = $res->num_rows > 0;

      $res->free();

      return $result;
    }

    public function CreateFromId($name, $baseid) {
      $newId = $this->Create($name, "");
      if( $newId )
      {
        // Create copy of image
        $su = $this->GetById($baseid);
        if( !$su ); // error but ignore for now

        if( $su['pict_id'] ) {
          $newId['picId'] = $this->db->Pictures()->CreateCopyFromId($su['pict_id'], $newId['id']);
        }

        return $newId;
      }
      return false;
    }

    public function Create($name, $pictureFileName) {
      $name = trim($name);
      $name = $this->db->sql->real_escape_string( $name );
      $query = "INSERT INTO `suppliers` (`name`) VALUES ('$name')";

      $res = $this->db->sql->query($query);

      if( $res === true ) {
        $newid = $this->db->sql->insert_id;

        // Create picture
        $picid = null;
        if( $pictureFileName != "" )
          $picid = $this->db->Pictures()->Create($newid, 'SU', $pictureFileName, false);

        return array('id' => $newid, 'picId' => $picid);

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
      $query = "SELECT f.id, f.name, f.urlTemplate, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM suppliers f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'SU' WHERE f.name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $su = $res->fetch_assoc();
      $res->free();

      return $su;
    }

    public function SetNameById($id, $name) {
      $name = trim($name);
      if( $name == "" )
        return;

      $name = $this->db->sql->real_escape_string($name);
      $query = "UPDATE suppliers SET name = '$name' WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }

    public function GetNameById($id) {

      $query = "SELECT name FROM suppliers WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $su = $res->fetch_assoc();
      $res->free();

      return $su['name'];
    }

    public function SetUrlById($id, $url) {
      $name = $this->db->sql->real_escape_string($url);
      $query = "UPDATE suppliers SET urlTemplate = '$url' WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }

    public function GetUrlById($id) {

      $query = "SELECT urlTemplate FROM suppliers WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $su = $res->fetch_assoc();
      $res->free();

      return $su['urlTemplate'];
    }

    public function GetById($id = null) {

      if( $id === null ) {
        // Get All
        $query = "SELECT f.id, f.name, f.urlTemplate, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM suppliers f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'SU' ORDER BY udf_NaturalSortFormat(f.name, 10, \".,\")";
      } else {
        $query = "SELECT f.id, f.name, f.urlTemplate, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM suppliers f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'SU' WHERE f.id = $id ORDER BY udf_NaturalSortFormat(f.name, 10, \".,\") LIMIT 1";
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
