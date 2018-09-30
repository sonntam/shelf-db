<?php
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get storelocations
  $storelocations = $pdb->StoreLocation()->GetEmpty();

	echo $pdb->RenderTemplate('page-editstorelocation.twig', array(
		'storeLocations' => $storelocations,
		'pageId' => 'showEmpty',
		'langLabels' => array(
			'mainHeader' => 'showEmptyStoreLocations'
		)
	));
?>
