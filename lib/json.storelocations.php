<?php

  include_once(dirname(__DIR__).'/classes/shelfdb.class.php');

  $_GET += array("id" => null);

  $fp = $pdb->StoreLocations()->GetById($_GET["id"]);

  $json = json_encode($fp, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;
?>
