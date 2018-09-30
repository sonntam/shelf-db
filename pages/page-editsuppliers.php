<?php
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get suppliers
  $suppliers = $pdb->Supplier()->GetAll();

	echo $pdb->RenderTemplate('page-editsuppliers.twig', array(
		'suppliers' => $suppliers
	));
?>
