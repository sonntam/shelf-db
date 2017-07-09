<?php

namespace ShelfDB {

  class Categories {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    public function AllReplaceParentId( int $oldid, int $newid ) {
      $query = "UPDATE categories SET parentnode = $newid WHERE parentnode = $oldid";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      // Update history
      $this->db->History()->Add(0, 'C', 'edit', 'parentnode', array(
        "parentnode" => $oldid,
        "parentname" => $this->GetNameById($oldid)
      ), array(
        "parentnode" => $newid,
        "parentname" => $this->GetNameById($newid)
      ) );

      return $res;
    }

    public function GetSubcategoryIdsFromId( int $rootcatid, $includeroot = false ) {

      if( $includeroot ) {
        $catids = array($rootcatid);
      } else {
        $catids = array();
      }

      $query = "SELECT id FROM categories WHERE parentnode = $rootcatid ORDER BY name ASC";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      $data = array_map( function($el) {
        return (int)($el['id']);
      }, $data);

      $catids = array_merge($catids, $data);

      foreach( $data as $catid ) {
        $subcats = $this->GetSubcategoryIdsFromId( $catid );
        $catids = array_merge($catids,$subcats);
      }

      return $catids;
    }

    public function GetParentFromId( int $catid = 0 ) {
      $query = "SELECT id, name FROM categories WHERE id = (SELECT parentnode FROM categories WHERE id = $catid)";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $parent = $res->fetch_assoc();
      $res->free();

      if( $parent === null )
      {
        $parent['id'] = 0;
        $parent['name'] = "root";
      }

      return $parent;
    }

    public function GetAsArray( int $baseid = 0, bool $withparent = false ) {

      // Get parent item
      if( $withparent && $baseid != 0 )
      {
          $query = "SELECT c.id, c.name, COUNT(p.id) as partcount FROM categories c LEFT JOIN parts p ON p.id_category = c.id WHERE c.id = $baseid GROUP BY c.id";

          $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

          $parent = $res->fetch_assoc();
          $res->free();

          $parent['id'] = intval($parent['id']);
          $parent['partcount']  = intval($parent['partcount']);
      }

      // Get all subitems
      $tree = $this->GetDirectChildrenFromId($baseid);

      if( $withparent && $baseid != 0 )
      {
        // Append
        $newtree[0] = $parent;
        $newtree[0]['children'] = $tree;
        $tree = $newtree;
      }

      // Build tree recursively
      foreach( $tree as &$node )
      {
          $node['id'] = intval($node['id']);
          $node['partcount']  = intval($node['partcount']);

          $children = $this->GetAsArray( (int)($node['id']), false );
          if( $children ) {
            $node['children'] = $children;
            for( $i = 0; $i < count($children); $i++ ) {
              $node['partcount'] += intval($children[$i]['partcount']);
            }
          }
      }

      return $tree;
    }

    public function GetById( int $id ) {

      $query = "SELECT * FROM `categories` WHERE `id` = $id";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $data = $res->fetch_assoc();
      $res->free();

      return $data;
    }

    public function GetNameById( int $id ) {

      if( $id == 0 )
        return "root";

      $query = "SELECT `name` FROM `categories` WHERE `id` = $id";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $name = $res->fetch_assoc();
      $res->free();

      return $name["name"];
    }

    public function SetNameById( int $id, string $name ) {

      $oldname = $this->GetNameById($id);
      if( !$oldname ) return false;

      $esname  = $this->db->sql->real_escape_string( $name );
      $query = "UPDATE `categories` SET `name` = '$esname' WHERE `id` = $id";

      $res = $this->db->sql->query($query) ;

      if( $res === true ) {
        // Everything OK
        $this->db->History()->Add($id, 'C', 'edit', 'name', $oldname, $name );

        return true;
      } else {
        // Error occured
        \Log::WarningSQLQuery($query, $this->db->sql);

        return false;
      }
    }

    public function DeleteById( int $id, $moveSiblingsUpwards = false ) {

      // Get siblings
      if( $moveSiblingsUpwards ) {
        $parent = $this->GetParentFromId($id);
        $this->AllReplaceParentId( $id, $parent['id'] );
      } else {
        // Delete siblings
        $siblings = $this->GetSubcategoryIdsFromId( $id, false );
        foreach( $siblings as $sibling ){
          $this->DeleteById( $sibling, false );
        }
      }

      // Delete this
      $query = "DELETE FROM categories WHERE id = $id;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res ) {
        $cat = $this->GetById($id);
        $this->db->History()->Add($id, 'C', 'delete', 'object', $cat, '');
      }

      return $res;
    }

    public function Create( int $parentId, string $name ) {
      $name = $this->db->sql->real_escape_string( $name );
      $query = "INSERT INTO `categories` (`name`, `parentnode`) VALUES ('$name', $parentId)";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res === true ) {
        $newid = $this->db->sql->insert_id;

        // Get parent
        $parentName = $this->GetNameGetNameById($parentId);

        // Add history
        $this->db->History()->Add($newid, 'C', 'create', 'object', '', array(
          "id" => $newid,
          "name" => $name,
          "parentnode" => $parentId,
          "parentname" => $parentName
        ));

        return $newid;

      } else {
        \Log::WarningSQLQuery($query, $this->db->sql);
        return false;
      }

    }

    public function MoveToParentById( int $id, int $newparentid ) {

      $cat = $this->GetById($id);

      if( !$cat ) return false;

      $query = "UPDATE `categories` SET `parentnode` = $newparentid WHERE `id` = $id";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( $res === true ) {
        // Everything OK
        $oldParentName = $this->GetNameById($cat["parentnode"]);
        $newParentName = $this->GetNameById($newparentid);

        $this->db->History()->Add($id,'C','edit','parentnode',array(
          "parentnode" => $cat["parentnode"],
          "parentname" => $oldParentName
        ),
        array(
          "parentnode" => $newparentid,
          "parentname" => $newParentName
        ));

        return true;
      } else {
        // Error occured
        \Log::WarningSQLQuery($query, $this->db->sql);

        return false;
      }
    }

    public function GetDirectChildrenFromId( int $catid = 0 ) {
      // SELECT c.*, COUNT(p.id) FROM categories c LEFT JOIN parts p ON p.id_category = c.id WHERE c.id = 2 GROUP BY c.id
      $query = "SELECT c.id, c.name, COUNT(p.id) as partcount FROM categories c LEFT JOIN parts p ON p.id_category = c.id WHERE c.parentnode = $catid GROUP BY c.id ORDER BY c.name ASC";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $children = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      return $children;
    }
  }
} // END NAMESPACE
?>
