<?php

  include_once(dirname(__DIR__).'/classes/shelfdb.class.php');

  $_GET += array("id" => null);

  $data = array_replace_recursive( array("id" => null), $_GET, $_POST );

  $fp = $pdb->Footprints()->GetById($data["id"]);

  $json = json_encode($fp, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;
?>
