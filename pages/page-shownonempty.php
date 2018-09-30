<?php

	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

	// Get storelocations
	$storelocations = $pdb->StoreLocation()->GetNonEmpty();

	echo $pdb->RenderTemplate('page-editstorelocation.twig', array(
		'storeLocations' => $storelocations,
		'pageId' => 'showNonEmpty',
		'langLabels' => array(
			'mainHeader' => 'showNonEmptyStoreLocations'
		),
		'hideAddButton' => true
	));
?>
