<?php

namespace ShelfDB {
  class Groups {

    private $db         = null;

    /** Constructor */
    function __construct($dbobj) {
      $this->db = $dbobj;
    }

    public function Create($name) {
      // Names must be unique
      if( $this->ExistsByName($name) )
        return null;

      $esname = $this->db->sql->real_escape_string($name);
      $query = "INSERT INTO groups (name) VALUES ('$esname');";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        $newid = $this->db->sql->insert_id;

        // Add history
        $this->History()->Add($newid, 'G', 'create', 'object', null, array(
          'id' => $newid,
          'name' => $name
        ));

        return $newid;
      } else {
        return null;
      }
    }

    public function GetDependancyGroups( int $groupId ) {
      // Get all groups that the given group is within directly or indirectly
      $query = "SELECT groupid FROM users_group WHERE objectId = $groupId;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      $newData = array_map( function($el) {
        return $el['groupid'];
      }, $data);

      foreach( $data as $group ) {
        $nextGroupId = $group['groupid'];
        $nextData = $this->GetDependandyGroups( $nextGroupId );

        // Assemble
        array_push($newData, $nextData );
      }

      return $newData;

    }

    public function AddGroupById(int $groupId, int $addedGroupId ) {
      // Disallow cyclic dependancies
      $srcDepGroups = $this->GetDependancyGroups( $addedGroupId );
      $tgtDepGroups = $this->GetDependancyGroups( $groupId );

      // Check if group to be added to is already somewhere in the dependancy path
      if( in_array( $groupId, $srcDepGroups ) )
        return false;

      // Check if group to be addedd is already within the groups dependancy path
      if( in_array( $addedGroupId, $tgtDepGroups ) )
        return false;

      // Add group to group's relations
      $query = "INSERT INTO users_groups (objectId, objectType, groupid) VALUES ($addedGroupId, 'G', $groupId);";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        foreach($userId as $user) {
          $this->History()->Add($groupid, 'G', 'addgroup', 'groupId', null, $addedGroupId );
        }
        return true;
      } else {
        return false;
      }
    }

    public function AddUserById(int $groupid, $userId) {
      // TODO Block ambiguous requests
      $existingUsers = $this->db->Users()->GetAllByGroupId($groupid);
      $userId = array_diff($userId, $existingUsers);
      $userId = array_unique($userId);
      $values = "(".join(",'U',$groupid),(", $userId).",'U',$groupid)";

      $query = "INSERT INTO users_groups (objectId, objectType, groupid) VALUES $values;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        foreach($userId as $user) {
          $this->History()->Add($groupid, 'G', 'adduser', 'userid', null, $user );
        }
        return true;
      } else {
        return false;
      }

    }

    public function GetById($id) {

      $query = "SELECT * FROM groups WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        $data = $res->fetch_assoc();
        $res->free();

        return $data;
      } else {
        return null;
      }
    }

    public function GetAll() {

      $query = "SELECT * FROM groups ORDER BY name ASC;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        return $data;
      } else {
        return null;
      }
    }

    public function GetNameById($id) {

      $query = "SELECT name FROM groups WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $fp = $res->fetch_assoc();
      $res->free();

      if( $fp ) {
        return $fp['name'];
      } else {
        return null;
      }
    }

    public function SetNameById(int $id, $newname) {
      $group = $this->ExistsByName($newname);

      if( $group ) {
        if( $group['id'] == $id ) {
          // Nothing changed
          return true;
        } else {
          // Names must be unique
          return false;
        }
      } else {
        // Change name
        $oldname   = $this->GetNameById($id);
        $esnewname = $this->db->sql->real_escape_string($newname);
        $query = "UPDATE groups SET name = ''$esnewname' WHERE id = $id;";
        $res   = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
        if( $res ) {
          $this->History()->Add( $id, 'G', 'edit', 'name', $oldname, $newname );
          return true;
        } else {
          return false;
        }
      }
    }

    public function GetAllFromUserId($id) {
      $query =
        "SELECT g.id, g.name FROM users_groups ug "
        ."LEFT JOIN groups g ON g.id = ug.groupid "
        //."LEFT JOIN users u ON u.id = ug.userid "
        ."WHERE ug.userid IN (".join(",",$id).")";


      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        if( $data && !is_array($data[0]) ) {
          $data = array($data);
        }

        return $data;
      } else {
        return null;
      }
    }

    public function ExistsByName($name) {
      $name = trim($name);
      if( $name == "" )
        return false;

      $name = $this->db->sql->real_escape_string($name);
      $query = "SELECT id FROM groups WHERE name = '$name';";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res->num_rows > 0 ) {
        $result = $res->fetch_all(MYSQLI_ASSOC);
      } else {
        $result = false;
      }
      $res->free();

      return $result;
    }
  }
}

?>
