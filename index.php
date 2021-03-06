<?php

  // This is the initial main controller

  include_once(__DIR__.'/lib/autoloader.php');

  \Log::Info("Request Uri ".$_SERVER['REQUEST_URI']);
  \Log::Info("Root ".$_SERVER['DOCUMENT_ROOT']);

  $pdb = ShelfDB::Instance();

  echo $pdb->RenderTemplate('index.twig', array(
    'version'          => array(
        'programVersion'   => $pdb->GetProgramVersion(),
        'databaseVersion'  => $pdb->GetDatabaseVersion()
    ),
    'logContent'       => Log::FetchLogContent(),
    'user'             => array(
      'isLoggedIn'         => $pdb->User()->IsLoggedIn(),
      'isAdmin'            => $pdb->User()->IsAdmin()
    )
  ));
?>
