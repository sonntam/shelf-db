<?php

namespace ShelfDB {
  class History {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    public function Add(int $itemId, $itemType, $action, $field, $oldValue, $newValue) {
      // $itemType can be one of G, U, P, SU, F, SL, PIC
      // Group, User, Part, Supplier, Footprint, Storelocation, Picture

      $userId = $this->db()->Users()->GetLoggedInUserId();
      if( !$userId ) $userId = 0; // No user is logged in

      $action   = $this->db()->sql->real_escape_string($action);
      $field    = $this->db()->sql->real_escape_string($field);
      $oldValue = ( is_string($oldValue) ?
          $this->db()->sql->real_escape_string($oldValue)
        : $this->db()->sql->real_escape_string(json_encode($oldValue))
      );
      $newValue = ( is_string($newValue) ?
          $this->db()->sql->real_escape_string($newValue)
        : $this->db()->sql->real_escape_string(json_encode($newValue))
      );
      $itemType = $this->db()->sql->real_escape_string($itemType);

      $query = "INSERT INTO history (userid, action, timestamp, itemid, itemtype, field, newvalue, oldvalue) "
        ."VALUES ($userId, '$action',NOW(), $itemId, '$itemType', '$field', '".$newValue."', '".$oldValue."');";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      return $res;
    }
  }
}

?>
