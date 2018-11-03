<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get Suppliers
  $suppliers = $pdb->Supplier()->GetAll();

/*
  foreach( $storelocs as &$s ) {
    $name = htmlspecialchars($s['name']);
    $s = "<li><a href='#' storename='".$name."' storeid=".$s['id']." data-rel='back' data-transition='flow' >".$name."</a></li>";
  }*/

  echo $pdb->RenderTemplate('popup-selectlistitemdialog.twig', array(
		"listItems" => $suppliers,
    "hideAddButton" => true,
    "langLabels" => array(
      "dialogHeader" => "popupSupplierSelectHeader",
      "dialogHeadline" => "popupSupplierSelectUserAction",
      "dialogMessage" => "popupSupplierFilterHint",
      "filterPlaceholder" => "popupSupplierFilterPlaceholder"
    )
	));
  return;
?>
