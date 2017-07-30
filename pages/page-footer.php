<?php

  require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

  // Dynamic page footer
  //

  // Show versions
  echo "Shelf-DB V".join(".",$pdb->GetProgramVersion())." using database V".join(".",$pdb->GetDatabaseVersion());
  \Log::Info("Request Uri ".$_SERVER['REQUEST_URI']);
  \Log::Info("Root ".$_SERVER['DOCUMENT_ROOT']);
  // Debug output if enabled
  echo "<br>".nl2br(Log::FetchLogContent());
?>
