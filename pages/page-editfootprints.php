<?php
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get footprints
  $footprints = $pdb->Footprint()->GetAll();

	echo $pdb->RenderTemplate('page-editfootprints.twig', array(
		'footprints' => $footprints
	));
?>
