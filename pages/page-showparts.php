<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	// Handle page arguments
	$defaults = array(
		'catid'              => 0,
		'search'             => null,
		'id'                 => null
	);

	$options = array_replace_recursive( $defaults, $_GET, $_POST );

	$search     = $options['search'];
	$searchMode = $search && ($search != '');

	$catid             = $options['catid'];
	$catname           = $pdb->Category()->GetNameById($catid);
	$showSubcategories = $options['showSubcategories'] == '1';

	// Create button from category node
	$funCreateButton = function($cat,$recurse) {
		return '<a style="margin: 0pt; padding: 0.4em" class="ui-btn ui-btn-inline ui-corner-all ui-shadow" href="page-showparts.php?catid='.$cat['id'].'&showSubcategories='.$recurse.'">'
		 .htmlspecialchars( $cat['name'] )."</a>\n";
	};

	// Get Parent
	$parent  				= $pdb->Category()->GetParentFromId($catid);
	$catParentName 	= $parent['name'];
	$catParentId    = $parent['id'];
	$catHasChildren = (int)( count($pdb->Category()->GetDirectChildrenFromId($catid)) > 0 );
	// Get All parent nodes and create buttons
	$buttons = [];
	if( $catid != 0 )
		$buttons[0] = $funCreateButton(array('id' => $catid, 'name' => $catname), $catHasChildren );

	$parents = [];
	while($parent['id'] != 0) {
		array_unshift($buttons, $funCreateButton($parent,1));
		array_unshift($parents, $parent);

		$parent  = $pdb->Category()->GetParentFromId($parent['id']);
	}

	// Filter strings
	$fpFilter = join(';', array_map(function($el){return $el['id'].':'.htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Footprint()->GetAll()));
	$slFilter = join(';', array_map(function($el){return $el['id'].':'.htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->StoreLocation()->GetAll()));
	$ctFilter = join(';', array_map(function($el){return $el['id'].':'.htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Category()->GetAll()));

	/*category.id
	category.name
	category.parents -> .id .name
	category.hasChildren

	footprintFilterString
	storeLocationFilterString
	categoryFilterString*/

	echo $pdb->RenderTemplate('page-showparts.twig', array(
		'category' => array(
			'id' => $catid,
			'name' => $catname,
			'hasChildren' => $catHasChildren,
			'parents' => $parents
		),
		'footprintFilterString' => $fpFilter,
		'storeLocationFilterString' => $slFilter,
		'categoryFilterString' => $ctFilter,
	));

	return;
?>
