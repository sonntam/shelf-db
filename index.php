<?php

  // This is the initial main controller

  include_once(__DIR__.'/lib/autoloader.php');

  \Log::Info("Request Uri ".$_SERVER['REQUEST_URI']);
  \Log::Info("Root ".$_SERVER['DOCUMENT_ROOT']);

  $pdb = ShelfDB::Instance();

  echo $pdb->RenderTemplate('index.twig');
  
?>
