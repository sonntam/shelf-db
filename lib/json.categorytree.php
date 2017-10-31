<?php

  /**
   * Generate ShelfDB tree of categories as JSON file
   */

  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $data = array_replace_recursive(
    array(
      "catid"      => 0,
      "withparent" => 0,
      "flat"       => false,
      'method'     => 'get'
    ), $_GET, $_POST );

  // Get category ID
  $pdb = ShelfDB::Instance();
  $catid      = $data["catid"];
  $withparent = $data["withparent"] === '1';

  if( strtolower($data["flat"]) === 'true' )
    $tree = $pdb->Category()->GetAsFlatNameArray($catid, $withparent);
  else
    $tree = $pdb->Category()->GetAsArray($catid, $withparent);

  switch( strtolower($data["method"]) ) {
    case "get":
      $result = json_encode($tree, JSON_PRETTY_PRINT);
      break;
    case "gridfilter":

      // Build html for grid filter
      $result = buildOptionHTMLFromArray($tree, 'id', 'name');

      break;
  }

  // Clear buffer and print JSON
  ob_clean();

  echo $result;

?>
