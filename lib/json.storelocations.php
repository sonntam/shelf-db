<?php

  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $_GET += array("id" => null);

  $fp = $pdb->StoreLocation()->GetById($_GET["id"]);

  $json = json_encode($fp, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;
?>
