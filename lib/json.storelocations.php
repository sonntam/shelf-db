<?php

  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Defaults
  $defaults = array(
    "id" => null,
    "method" => "get",
    "anyName" => null
  );

  $data = array_replace_recursive( $defaults, $_GET, $_POST );

  // Get the data
  $pdb = ShelfDB::Instance();
  $fp = $pdb->StoreLocation()->GetById($_GET["id"]);

  switch( strtolower($data["method"]) ) {
    case "get":
      $result = json_encode($fp, JSON_PRETTY_PRINT);
      break;
    case "gridfilter":

      // Add the "any" option if requested
      if( $data["anyName"] ){
        array_unshift( $fp, array('name' => $data["anyName"]));
      }

      // Build html for grid filter
      $result = buildOptionHTMLFromArray($fp, 'id', 'name');

      break;
  }

  // Clear buffer and print JSON
  ob_clean();

  echo $result;
?>
