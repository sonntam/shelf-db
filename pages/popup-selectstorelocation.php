<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get footprints
  $storelocs = $pdb->StoreLocation()->GetAll();

  echo $pdb->RenderTemplate('popup-selectlistitemdialog.twig', array(
		"listItems" => $storelocs,
    "hideAddButton" => false,
    "langLabels" => array(
      "dialogHeader" => "popupStoreLocationSelectHeader",
      "dialogHeadline" => "popupStoreLocationSelectUserAction",
      "dialogMessage" => "popupStoreLocationFilterHint",
      "filterPlaceholder" => "popupStoreLocationFilterPlaceholder"
    )
	));
  return;
?>
