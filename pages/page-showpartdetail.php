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

		$parentCategoryNames = array_map( function($x) { return $x['name']; }, $parentCategories );
		$parentCategoryLinks = array_map( function($x) {
			return '<a href="page-showparts.php?catid='.$x['id'].'&showSubcategories=1">'.$x['name'].'</a>';
		}, $parentCategories );

		$picFnames = ( $part['pict_id_arr'] ? explode("/", $part['pict_fname_arr']) : array() );
		$picIds    = ( $part['pict_id_arr'] ? explode(",", $part['pict_id_arr']) : array() );
		$picMaster = ( $part['pict_id_arr'] ? explode(",", $part['pict_masterpict_arr']) : array() );

		$arrPics = array();
		for( $i = 0; $i < sizeof($picFnames); $i++ ) {
			$arrPics[] = array(
				'id' => $picIds[$i],
				'fname' => $picFnames[$i],
				'master' => $picMaster[$i],
			 	'imgPath' => $pdb->RelRoot().'img/parts/'.$picFnames[$i] );
		}

		$partImageHtml = array_map( function($x) use ($pdb) {
			$imgPath = $pdb->RelRoot().'img/parts/'.$x['fname'];
			ob_start();
			?>
				<div name="pictureContainer" value="<?php echo $x['id']; ?>" style="vertical-align: top; display: inline-block; text-align: center">
					<a href="#popupimg" data-rel="popup" data-position-to="window">
						<img id="picture-<?php echo $x['id']; ?>" class="partinfo partImageListItem" data-other-src="<?php echo $imgPath; ?>" src="<?php echo $imgPath; ?>">
					</a>
					<div data-role="controlgroup" data-type="horizontal" data-mini="true">
						<input type="checkbox" <?php if($x['master']) { echo 'checked="checked"'; } ?> altname="masterPicCheckbox" name="masterPicSelect-<?php echo $x['id']; ?>" id="masterPicSelect-<?php echo $x['id']; ?>">
						<label for="masterPicSelect-<?php echo $x['id']; ?>" uilang="masterImage"></label>
						<a href="#" name="deletePicture" value="<?php echo $x['id']; ?>" class="ui-btn ui-corner-all ui-icon-delete ui-btn-icon-left" uilang="delete"></a>
					</div>
				</div>
			<?php
			$el = ob_get_clean();
			return $el;
		}, $arrPics);

		// Build category string
		$categoryString = join( " <i class='fa fa-arrow-right'></i> ", $parentCategoryLinks);

		// Link to supplier
		$url = $pdb->Supplier()->GetUrlFromId($part['id_supplier'], $part['supplierpartnr']);
	}

	echo $pdb->RenderTemplate('page-showpartdetail.twig', array(
		"part" => array_merge( $part, array(
			"showQr" => ConfigFile\QRCode::$enable,
			"qrImgData" => $pdb->Part()->CreateQRCode($data['partid']),
			"category" => array(
				"name" => $part['category_name'],
				"id" => $part['id_category']
			),
			"priceFormatted" => $pdb->Part()->FormatPrice($part['price']),
			"historyString" => $pdb->History()->PrintHistoryData($pdb->History()->GetByTypeAndId($data['partid'], 'P')),
			"images" => $arrPics,
			"supplierImgFile" => $partSupplierImageFile,
	    "footprintImgFile" => $partFootprintImageFile
		))
	));
	return;
?>

<div id=showpartdetail data-role="page">
	<script>

	function addPictureContainer( id, imgPath, thumbPath ) {
		$('<div/>', {
			name: "pictureContainer",
			value: id,
			style: "vertical-align: top; display: inline-block; text-align: center"
		}).append(
			$('<a/>',{
				href: "#popupimg",
				"data-rel": "popup",
				"data-position-to": "window"
			}).append(
				$('<img/>', {
					id: "picture-" + id,
					class: "partinfo partImageListItem",
					"data-other-src": <?php echo '"'.$pdb->RelRoot().'img/parts/"'; ?>+imgPath,
					src: <?php echo '"'.$pdb->RelRoot().'img/parts/"'; ?>+thumbPath
				})
			),
			$('<div/>',{
				"data-role": "controlgroup",
				"data-type": "horizontal",
				"data-mini": true
			}).append(
				$('<input/>',{
					type: "checkbox",
					altname: "masterPicCheckbox",
					name: "masterPicSelect-"+id,
					id: "masterPicSelect-"+id
				}),
				$('<label/>',{
					for: "masterPicSelect-"+id,
					uilang: "masterImage"
				}),
				$('<a/>',{
					href: "#",
					name: "deletePicture",
					value: id,
					class: "ui-btn ui-corner-all ui-icon-delete ui-btn-icon-left",
					uilang: "delete"
				})
			)
		).insertBefore($('[name=pictureContainer][value=add]'));

		// Refresh
		Lang.searchAndReplace();
		$('[name=partPictureListView]').enhanceWithin();

	}

	</script>
