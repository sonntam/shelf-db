<?php

  require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  // Dynamic page footer
  //

  // Show versions
  echo "Part-DB V".join(".",$pdb->GetProgramVersion())." using database V".join(".",$pdb->GetDatabaseVersion());

  // Debug output if enabled
  echo "<br><br>".nl2br(Log::FetchLogContent());

?>
