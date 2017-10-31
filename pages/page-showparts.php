<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

	// Handle page arguments
	$defaults = array(
		'catid'              => 0
	);

	$options = array_replace_recursive( $defaults, $_GET, $_POST );

	$pdb = ShelfDB::Instance();

	$catid             = $options['catid'];
	$catname           = $pdb->Category()->GetNameById($catid);

		// Get Parent
	$parent  				= $pdb->Category()->GetParentFromId($catid);
	$catHasChildren = (int)( count($pdb->Category()->GetDirectChildrenFromId($catid)) > 0 );

	// Generate list of parents
	$parents = [];
	while($parent['id'] != 0) {
		array_unshift($parents, $parent);
		$parent  = $pdb->Category()->GetParentFromId($parent['id']);
	}

	/*category.id
	category.name
	category.parents -> .id .name
	category.hasChildren
 */

	echo $pdb->RenderTemplate('page-showparts.twig', array(
		'category' => array(
			'id'          => $catid,
			'name'        => $catname,
			'hasChildren' => $catHasChildren,
			'parents'     => $parents
		)
	));

	return;
?>
