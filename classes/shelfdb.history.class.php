<?php

namespace ShelfDB {
  class History {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    public function Add(int $itemId, $itemType, $action, $field, $oldValue, $newValue) {

      $userId = $this->db->Users()->GetLoggedInUserId();
      if( !$userId ) $userId = 0; // No user is logged in

      $action   = $this->db->sql->real_escape_string($action);
      $field    = $this->db->sql->real_escape_string($field);
      $oldValue = $this->db->sql->real_escape_string($oldValue);
      $newValue = $this->db->sql->real_escape_string($newValue);
      $itemType = $this->db->sql->real_escape_string($itemType);

      $query = "INSERT INTO history (userid, action, timestamp, itemid, itemtype, field, newvalue, oldvalue) "
        ."VALUES ($userId, '$action',NOW(), $itemId, '$itemType', '$field', '".json_encode($newValue)."', '".json_encode($oldValue)."');";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }
  }
}

?>
