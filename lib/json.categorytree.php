<?php

  /**
   * Generate ShelfDB tree of categories as JSON file
   */

  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $data = array_replace_recursive(
    array(
      "catid"      => 0,
      "withparent" => 0
    ), $_GET, $_POST );

  // Get category ID
  $catid      = $data["catid"];
  $withparent = $data["withparent"];

  $tree = $pdb->Category()->GetAsArray($catid, $withparent == 1);

  $json = json_encode($tree, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;

?>
