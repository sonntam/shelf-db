<?php

namespace ShelfDB {

  class Category {

    private $db = null;
    private $data = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    private function GetData() {

      // 1. Check if it is in memory
      if( $this->data )
        return $this->data;

      // 2. Get from cache
      if( $this->db()->GetCache()->isCached('categories') ) {
        $this->data = $this->db()->GetCache()->getCached('categories');
        return $this->data;
      }

      // 3. Read from database
      $query = "SELECT c.id, c.parentnode as parent, c.name, COUNT(p.id) as partcount FROM categories c LEFT JOIN parts p ON p.id_category = c.id GROUP BY c.id ORDER BY c.name ;";
      //$query = "SELECT id, parentnode as parent, name FROM categories ORDER BY name ASC;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
        return null;

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      $this->data = new \BlueM\Tree($data);

      // Propagate part counts to parent categories
      $partCountSum = function( $node ) use ( &$partCountSum ) {

        $children = $node->getChildren();

        foreach( $children as $child ) {
          $node->set('partcount', $node->get('partcount') + $partCountSum($child));
        }
        return $node->get('partcount');
      };

      $children = $this->data->getRootNodes();
      foreach( $children as $child ) {
          $partCountSum($child);
      }

      //$partCountSum($this->data);

      // Cache this data
      $this->db()->GetCache()->storeCached("categories", $this->data);

      return $this->data;
    }

    public function AllReplaceParentId( int $oldid, int $newid ) {
      $query = "UPDATE categories SET parentnode = $newid WHERE parentnode = $oldid";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      // Update history
      $this->db()->History()->Add(0, 'C', 'edit', 'parentnode', array(
        "parentnode" => $oldid,
        "parentname" => $this->GetNameById($oldid)
      ), array(
        "parentnode" => $newid,
        "parentname" => $this->GetNameById($newid)
      ) );

      if( $res )
        $this->DefileCache();

      return $res;
    }

    public function GetSubcategoryIdsFromId( int $rootcatid, $includeroot = false ) {

      $descendants =  $this->GetData()->getNodeById($rootcatid)->getDescendants($includeroot);

      $descendants = array_map(
        function($x) {
          return $x->get('id');
        }, $descendants );

      return $descendants;
    }

    public function GetParentFromId( int $catid = 0 ) {

      $node = $this->GetData()->getNodeById($catid);

      if( !$node ) return null;

      $parent = $node->getParent();

      if( $parent ) {
        $parent = $parent->toArray();
        if( isset($parent['name']) )
          return $parent;
        else
          return array( "id" => 0, "name" => "root" );
      } else {
        return false;
      }
    }

    public function GetAncestorsFromId( int $catid, $includeself = false ) {
      if( $catid <= 0 )
        return null;

      $node = $this->GetData()->getNodeById($catid);

      if( !$node ) return null;

      $ancestors = $node->getAncestors($includeself);

      return array_filter( array_map(
        function($x) {
          return $x->toArray();
        }, $ancestors ),
        function($x) {
          return $x['id'] > 0;
        });
    }

    public function GetAsFlatNameArray( int $baseid = 0, bool $withparent = false ) {
      // Get in array form first
      $cattree = $this->GetAsArray( $baseid, $withparent );

      $walkFcn = function($cattree, int $level = 0) use (&$walkFcn) {
        $return = array();
        foreach( $cattree as $cat ) {
          $return[] = array('id' => $cat['id'], 'name' => str_repeat(' ',$level*2).$cat['name'] );
          if( isset($cat['children']) && count($cat['children']) > 0 ) {
            $return = array_merge( $return, $walkFcn($cat['children'],$level+1) );
          }
        }
        return $return;
      };

      // Walk the tree
      $cattree = $walkFcn($cattree);

      return $cattree;
    }

    public function GetAsArray( int $baseid = 0, bool $withparent = false ) {


      $array = array();
      $nodes = $this->GetData()->getNodeById($baseid)->getChildren();

      foreach($nodes as $node) {
        $el = $node->toArray();
        $el['children'] = $this->GetAsArray($el['id']);
        $array[] = $el;
      }

      if( $withparent ) {
        $parent = $this->GetData()->getNodeById($baseid)->toArray();
        $parent['children'] = $array;
        $array = array($parent);
      }

      return $array;
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

    public function GetById( $id = null ) {

      $data = $this->GetData();

      if( $id === null ) {
        $data = $data->getNodes();
        $data = array_map(
          function($x) {
            return $x->toArray();
          }, $data);
      } else {
        $data = $data->getNodeById($id)->toArray();
      }

      if( sizeof($data) == 1 ) {
        $data = $data[0];
      }

      return $data;
    }

    public function GetNameById( int $id ) {

      if( $id == 0 )
        return "root";

      $query = "SELECT `name` FROM `categories` WHERE `id` = $id";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $name = $res->fetch_assoc();
      $res->free();

      return $name["name"];
    }

    public function SetNameById( int $id, string $name ) {

      $oldname = $this->GetNameById($id);
      if( !$oldname ) return false;

      $esname  = $this->db()->sql->real_escape_string( $name );
      $query = "UPDATE `categories` SET `name` = '$esname' WHERE `id` = $id";

      $res = $this->db()->sql->query($query) ;

      if( $res === true ) {
        // Everything OK
        $this->db()->History()->Add($id, 'C', 'edit', 'name', $oldname, $name );
        $this->DefileCache();

        return true;
      } else {
        // Error occured
        \Log::WarningSQLQuery($query, $this->db()->sql);

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
          if( !$this->DeleteById( $sibling, false ) ) {
            return null;
          }
        }
      }

      // Delete this
      $query = "DELETE FROM categories WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        $cat = $this->GetById($id);
        $this->db()->History()->Add($id, 'C', 'delete', 'object', $cat, '');
        $this->DefileCache();
      }

      return $res;
    }

    public function Create( int $parentId, string $name ) {
      $name = $this->db()->sql->real_escape_string( $name );
      $query = "INSERT INTO `categories` (`name`, `parentnode`) VALUES ('$name', $parentId)";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res === true ) {
        $newid = $this->db()->sql->insert_id;

        // Get parent
        $parentName = $this->GetNameById($parentId);

        // Add history
        $this->db()->History()->Add($newid, 'C', 'create', 'object', '', array(
          "id" => $newid,
          "name" => $name,
          "parentnode" => $parentId,
          "parentname" => $parentName
        ));

        $this->DefileCache();

        return $newid;

      } else {
        \Log::WarningSQLQuery($query, $this->db()->sql);
        return false;
      }

    }

    public function DefileCache() {
      $this->db()->GetCache()->deleteCached("categories");
    }

    public function MoveToParentById( int $id, int $newparentid ) {

      $cat = $this->GetById($id);

      if( !$cat ) return false;

      $query = "UPDATE `categories` SET `parentnode` = $newparentid WHERE `id` = $id";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res === true ) {
        // Everything OK
        $oldParentName = $this->GetNameById($cat["parent"]);
        $newParentName = $this->GetNameById($newparentid);

        $this->db()->History()->Add($id,'C','edit','parentnode',array(
          "parentnode" => $cat["parent"],
          "parentname" => $oldParentName
        ),
        array(
          "parentnode" => $newparentid,
          "parentname" => $newParentName
        ));

        $this->DefileCache();

        return true;
      } else {
        // Error occured
        \Log::WarningSQLQuery($query, $this->db()->sql);

        return false;
      }
    }

    public function GetDirectChildrenFromId( int $catid = 0 ) {

      $node = $this->GetData()->getNodeById($catid);

      if( !$node ) return null;

      $children = $node->getChildren();

      return array_map(
        function($x) {
          return $x->toArray();
        }, $children );
    }
  }
} // END NAMESPACE
?>
