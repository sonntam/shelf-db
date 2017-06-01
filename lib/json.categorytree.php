<?php

  /**
   * Generate PartDB tree of categories as JSON file
   */

  include_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  $_GET += array("catid" => 0);
  $_GET += array("withparent" => 0);

  // Get category ID
  $catid      = $_GET["catid"];
  $withparent = $_GET["withparent"];

  $tree = $pdb->GetCategoriesAsArray($catid, $withparent == 1);

  $json = json_encode($tree, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;

?>
