<?php

  include_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  $_GET += array("id" => null, "partNr" => null);

  $fp = $pdb->Suppliers()->GetById($_GET["id"]);
  $fp['urlTemplate'] = $pdb->Suppliers()->ExpandRawUrl( $fp['urlTemplate'], $_GET['partNr'] );

  $json = json_encode($fp, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;
?>
