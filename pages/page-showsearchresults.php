<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

	$defaults = array(
		'search'             => null,
		'id'                 => null,
		'searchMode' 				 => 'globalString'
	);

	$pdb = ShelfDB::Instance();

	$options = array_replace_recursive( $defaults, $_GET, $_POST );

	switch( $options['searchMode'] ) {
		case 'globalString':
			// Global search
			$searchString = $options['search'];
			$filterArguments = array(
				'globalSearchString' => $searchString,
			);
			break;

		case 'storageLocationId':
			$storelocId = $options['search'];

			if( $sl = $pdb->StoreLocation()->GetById($storelocId) ) {
			} else {
				$sl = array(
					'name' => 'undefined',
					'id' => 0
				);
			}
			$searchString = $sl['name'];
			$filterArguments = array(
				'searchField' => 'storelocid',
				'searchOper' => 'eq',
				'searchString' => $sl['id'],
			);

			break;

		case 'footprintId':
			$footprintId = $options['search'];

			if( $fp = $pdb->Footprint()->GetById($footprintId) ) {
			} else {
				$fp = array(
					'name' => 'undefined',
					'id' => 0
				);
			}
			$searchString = $fp['name'];
			$filterArguments = array(
				'searchField' => 'footprintid',
				'searchOper' => 'eq',
				'searchString' => $fp['id'],
			);
			break;
		case 'supplierId':
			$supplierId = $options['search'];
			if( $su = $pdb->Supplier()->GetById($supplierId) ) {
			} else {
				$su = array(
					'name' => 'undefined',
					'id' => 0
				);
			}
			$searchString = $su['name'];
			$filterArguments = array(
				'searchField' => 'supplierid',
				'searchOper' => 'eq',
				'searchString' => $su['id'],
			);
			break;

		default:
			return;
	}

	/*
	searchString
	filterArguments

	footprintFilterString
	storeLocationFilterString
	categoryFilterString
	*/

	echo $pdb->RenderTemplate('page-showsearchresults.twig', array(
		'filterArguments' => $filterArguments,
		'searchString' => $searchString
	));

	exit;
?>
