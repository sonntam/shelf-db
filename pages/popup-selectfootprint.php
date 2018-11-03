<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get footprints
  $footprints = $pdb->Footprint()->GetAll();

  echo $pdb->RenderTemplate('popup-selectlistitemdialog.twig', array(
		"listItems" => $footprints,
    "hideAddButton" => true,
    "langLabels" => array(
      "dialogHeader" => "popupFootprintSelectHeader",
      "dialogHeadline" => "popupFootprintSelectUserAction",
      "dialogMessage" => "popupFootprintFilterHint",
      "filterPlaceholder" => "popupFootprintFilterPlaceholder"
    )
	));
  return;
?>
