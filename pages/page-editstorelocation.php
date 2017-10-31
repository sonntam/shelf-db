<?php
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get storelocations
  $storelocations = $pdb->StoreLocation()->GetAll();

	echo $pdb->RenderTemplate('page-editstorelocation.twig', array(
		'storeLocations' => $storelocations
	));
?>
