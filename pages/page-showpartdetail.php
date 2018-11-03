<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

	$data = array_replace_recursive(
		array(
			'partid' => null
		), $_GET, $_POST );

  if( $data["partid"] != null )
	{
    $part = $pdb->Part()->GetDetailsById($data["partid"]);
		$name = $part['name'];
		$partFootprintImageFile = joinPaths( $pdb->RelRoot(), 'img/footprint', $part['f_pict_fname']);
		$partSupplierImageFile = joinPaths( $pdb->RelRoot(), 'img/supplier', $part['su_pict_fname']);
		$parentCategories = array_reverse( $pdb->Category()->GetAncestorsFromId($part['id_category'], true) );

		$picFnames = ( $part['pict_id_arr'] ? explode("/", $part['pict_fname_arr']) : array() );
		$picIds    = ( $part['pict_id_arr'] ? explode(",", $part['pict_id_arr']) : array() );
		$picMaster = ( $part['pict_id_arr'] ? explode(",", $part['pict_masterpict_arr']) : array() );

		$dsFnames = ( $part['datasheet_id_arr'] ? explode("/", $part['datasheet_fname_arr']) : array() );
		$dsIds    = ( $part['datasheet_id_arr'] ? explode(",", $part['datasheet_id_arr']) : array() );

		$arrPics = array();
		for( $i = 0; $i < sizeof($picFnames); $i++ ) {
			$arrPics[] = array(
				'id' => $picIds[$i],
				'fname' => $picFnames[$i],
				'master' => $picMaster[$i],
			 	'imgPath' => $pdb->RelRoot().'img/parts/'.$picFnames[$i]
			);
		}

		$arrDatasheets = array();
		for( $i = 0; $i < sizeof($dsFnames); $i++ ) {
			$arrDatasheets[] = array(
				'id' => $dsIds[$i],
				'fname' => $dsFnames[$i],
			 	'datasheetPath' => joinPaths($pdb->RelRoot(),$pdb->Datasheet()->GetDatasheetFolder(),$dsFnames[$i])
			);
		}

	}

	echo $pdb->RenderTemplate('page-showpartdetail.twig', array(
		"part" => array_merge( $part, array(
			"showQr" => ConfigFile\QRCode::$enable,
			"qrImgData" => $pdb->Part()->CreateQRCode($data['partid']),
			"category" => array(
				"name" => $part['category_name'],
				"id" => $part['id_category']
			),
			"categoryTree" => $parentCategories,
			"priceFormatted" => $pdb->Part()->FormatPrice($part['price']),
			"historyString" => $pdb->History()->PrintHistoryData($pdb->History()->GetByTypeAndId($data['partid'], 'P')),
			"images" => $arrPics,
			"datasheets" => $arrDatasheets,
			"supplierImgFile" => $partSupplierImageFile,
	    "footprintImgFile" => $partFootprintImageFile
		))
	));
	return;
?>
