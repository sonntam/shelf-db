<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	$data = array_replace_recursive(
		array(
			'partid' => null
		), $_GET, $_POST );

  if( $data["partid"] != null )
	{
    $part = $pdb->Parts()->GetDetailsById($data["partid"]);
		$name = $part['name'];
		$partFootprintImageFile = joinPaths( $pdb->RelRoot(), 'img/footprint', $part['f_pict_fname']);
		$partSupplierImageFile = joinPaths( $pdb->RelRoot(), 'img/supplier', $part['su_pict_fname']);
		$parentCategories = array_reverse( $pdb->Categories()->GetAncestorsFromId($part['id_category'], true) );

		$parentCategoryNames = array_map( function($x) { return $x['name']; }, $parentCategories );
		$parentCategoryLinks = array_map( function($x) {
			return '<a href="page-showparts.php?catid='.$x['id'].'&showSubcategories=1">'.$x['name'].'</a>';
		}, $parentCategories );

		$picFnames = ( $part['pict_id_arr'] ? explode("/", $part['pict_fname_arr']) : array() );
		$picIds    = ( $part['pict_id_arr'] ? explode(",", $part['pict_id_arr']) : array() );
		$picMaster = ( $part['pict_id_arr'] ? explode(",", $part['pict_masterpict_arr']) : array() );

		$arrPics = array();
		for( $i = 0; $i < sizeof($picFnames); $i++ ) {
			$arrPics[] = array('id' => $picIds[$i], 'fname' => $picFnames[$i], 'master' => $picMaster[$i] );
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
		$url = $pdb->Suppliers()->GetUrlFromId($part['id_supplier'], $part['supplierpartnr']);
	}

?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=showpartdetail data-role="page">
	<script>
	var partId = <?php echo $data['partid']; ?>;

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

	function updateButtons() {
		var total = $('[name=showTotal]');
		var stock = $('[name=showStock]');

		var nTotal = parseInt(total.val());
		var nStock = parseInt(stock.val());

		if( nTotal <= nStock  || nTotal <= 0 )	// Disable minus total button
			$('[name=subTotal]').button('disable');
		else
			$('[name=subTotal]').button('enable');

		if( nStock >= nTotal )
			$('[name=addStock]').button('disable');
		else
			$('[name=addStock]').button('enable');

		if( nStock <= 0 )
			$('[name=subStock]').button('disable');
		else
			$('[name=subStock]').button('enable');
	}

	// Popup handler
	$.mobile.pageCreateTasks.push( function() {

		// Set controls for available part numbers
		updateButtons();

	  $( ".photopopup" ).on({
      popupbeforeposition: function(evt) {
        var maxHeight = $( window ).height() - 60 + "px";

       	$( ".photopopup img" ).css( "max-height", maxHeight );
    	}
	  });


		$('.ui-content').on('click', 'a', function(evt) {
				// Set data
				var $tn = $(evt.target);

				$( ".photopopup img" ).attr('src', $tn.attr('data-other-src'));

		});

		$('[name=pictureContainerAddButton]').click( function(e) {
			// Show upload image dialog
			ShelfDB.GUI.Popup.openExternalPopup({
				url: '<?php echo $pdb->RelRoot(); ?>pages/popup-uploadfile.php',
				forceReload: true,
				fixedMaxWidth: '600px',
				postdata: {
					id: <?php echo $data['partid']; ?>,
		    	method: 'addPicture',
					itemtype: 'part',
		   		type: 'picture'
				},
				customEventName: 'positiveResponse',
				customEventHandler: function( e, data ) {
					if( data && data.success ) {
						// Dynamically add picture to list (before plus button)
						// data.imageFileName
						// data.pictureId
						// data.thumbFileName
						addPictureContainer(data.pictureId, data.imageFileName, data.imageFileName);
					}
				}
			});
		});

		$('[name=partPictureListView]').on('click','a[name=deletePicture]', function(e) {

			var id = $(this).attr('value');
			debugger;
			ShelfDB.GUI.Popup.confirmPopUp({
		    header: Lang.get('editPartDeletePicture'),
		    text: Lang.get('noUndoHint'),
		    confirmButtonText: Lang.get('delete'),
		    confirm: function() {
					$.mobile.referencedLoading('show');
					$.ajax({
						url: '<?php echo $pdb->RelRoot(); ?>lib/edit-part.php',
						type: 'POST',
						dataType: 'json',
						data: {
							id: '<?php echo $data["partid"]; ?>',
							method: 'deletePicture',
							pictureId: id
						}
					}).done(function(data) {
						// Update gui
						if( data && data.success ) {

							$('[name=pictureContainer][value='+data.pictureId+']').remove();
							// Update main picture
							$.mobile.referencedLoading('show');
							$.ajax({
								url: '<?php echo $pdb->RelRoot(); ?>lib/json.parts.php',
								type: 'POST',
								dataType: 'json',
								data: {
									partid: '<?php echo $data["partid"]; ?>',
									getDetailed: true
								}
							}).done(function(data) {
								$('.partimage').attr('src',data.mainPicFile);
								$('.partimage').attr('data-other-src',data.mainPicFile);

								$.mobile.referencedLoading('hide');
							});
						}
						$.mobile.referencedLoading('hide');
					});
				}
			});

		});

		$('[name=partPictureListView]').on('change','input[altname=masterPicCheckbox]', function (e) {
			this.checked = !this.checked;

			e.preventDefault();
			e.stopPropagation();

			if( this.checked ) return;

			var that = this;

			$.mobile.referencedLoading('show');
			$.ajax({
				url: '<?php echo $pdb->RelRoot(); ?>lib/edit-part.php',
				type: 'POST',
				dataType: 'json',
				data: {
					id: $(this).attr('id').split('-')[1],
					method: 'setMasterPic'
				}
			}).done(function(data) {
				// Update gui
				if( data && data.success ) {
					// Remove checks from all other checkboxes and check own
					$('[altname=masterPicCheckbox]').prop('checked', false).checkboxradio('refresh');
					$(that).prop('checked', true).checkboxradio('refresh');

					// Update main image right away
					$('.partimage').attr('src',$('#picture-'+data.id).attr('src'));
					$('.partimage').attr('data-other-src',$('#picture-'+data.id).attr('data-other-src'));
				}
				$.mobile.referencedLoading('hide');
			});

		});

		$('[name=showSupplier]').click(function(evt) {
			var url = $(this).attr('url');
			if( url )
				window.open(url,'_blank');
		});

		$('[name=editPartNumber]').click(function(evt) {
			ShelfDB.GUI.Popup.inputPopUp({
					header: Lang.get('editPartPartNumber'),
					headline: Lang.get('editPartChangePartNumber'),
					textPlaceholder: Lang.get('enterPartNumber'),
					textDefault: $('[name=showPartNumber]').val(),
					ok: function( newnumber ) {
						// Apply new data in database
						ShelfDB.Parts.editPartFieldData( partId, 'supplierpartnr', newnumber,
							function(data) {
								if( data && data.success ) {
									$('[name=showPartNumber]').val(newnumber);
									// Get url for part and update picture
									//
									$.shelfdb.getSupplierByIdAsync({
										id: $('[name=showSupplier]').attr('supplierId'),
										partNr: newnumber,
										done: function(data) {
											// Update gui
											if( data ) {
												$('[name=showSupplier]').attr('url',data.urlTemplate);
											}
										}
									});
								}
							}
						);
					}
			});
		});

		$('[name=editName]').click(function(evt) {
			ShelfDB.GUI.Popup.inputPopUp({
		    header: Lang.get('editPartNewName'),
		    headline: Lang.get('editPartChangeName'),
		    textPlaceholder: Lang.get('enterName'),
		    textDefault: $('[name=showName]').text(),
		    ok: function( newName ) {
					// Apply new data in database
					ShelfDB.Parts.editPartFieldData( partId, 'name', newName,
						function(data) {
							if( data && data.success ) {
								$('[name=showName]').text(newName);
							}
						}
					);
				}
			});
		});

		$('[name=deletePart]').click(function(evt) {
			ShelfDB.GUI.Popup.confirmPopUp({
		    header: Lang.get('editPartDelete'),
		    text: Lang.get('noUndoHint'),
		    confirmButtonText: Lang.get('delete'),
		    confirm: function() {
					// TODO Submit and delete
					alert('TODO Delete part');
				}
			});
		});

		$('[name=editStoreloc]').click(function(evt) {
			ShelfDB.GUI.Popup.openExternalPopup({
				forceReload: true,
				url: '<?php echo $pdb->RelRoot(); ?>pages/popup-selectstorelocation.php',
				afteropen: function(evt) {
					$(evt.target).find("input").first().focus().select();
				},
				afterclose: function(evt) {
					//alert(JSON.stringify(evt));
				},
				click: function(evt) {
					var storeClicked = $(evt.currentTarget).attr('storeid');
					if( storeClicked )
					{
						ShelfDB.Parts.editPartFieldData( partId, 'storelocation', storeClicked,
							function(data) {
								if( data && data.success ) {
									//evt.preventDefault();
									// Load store location name and store in database
									$('[name=showStoreloc]').attr('value',$(evt.currentTarget).attr('storename'));
								}
							}
						);
					}
				}
			});
		});

		$('[name=editFootprint]').click(function(evt) {
			ShelfDB.GUI.Popup.openExternalPopup({
				forceReload: true,
				url: '<?php echo $pdb->RelRoot(); ?>pages/popup-selectfootprint.php',
				afteropen: function(evt) {
					$(evt.target).find("input").first().focus().select();
				},
				afterclose: function(evt) {
				},
				click: function(evt) {
					var fpClicked = $(evt.currentTarget).attr('footprintid');
					if( fpClicked )
					{
						ShelfDB.Parts.editPartFieldData( partId, 'footprint', fpClicked,
							function(data) {
								if( data && data.success ) {
									// Load store location name and store in database
									$('[name=showFootprint]').attr('value',$(evt.currentTarget).attr('footprintname'));
									// Update pictures
									$.ajax({	// Main picture if necessary
										url: '<?php echo $pdb->RelRoot(); ?>lib/json.parts.php',
										type: 'POST',
										dataType: 'json',
										data: {
											partid: <?php echo $data["partid"]; ?>,
											getDetailed: true
										}
									}).done(function(data) {
										// Update gui
										if( data ) {
											var imgFile = data.mainPicFile;
											$('.partimage').attr({
												src: imgFile,
												'data-other-src': imgFile
											});
										}
									});
									$.ajax({
										url: '<?php echo $pdb->RelRoot(); ?>lib/json.footprints.php',
										type: 'POST',
										dataType: 'json',
										data: {
											id: fpClicked
										}
									}).done(function(data) {
										// Update gui
										if( data ) {
											var imgFile = '<?php echo $pdb->RelRoot(); ?>img/footprint/'+data['pict_fname'];
											$('[name=imgFootprint]').attr({
												src: imgFile,
												'data-other-src': imgFile
											});
										}
									});
								}
							}
						);
					}
				}
			});
		});

		$('[name=editSupplier]').click(function(evt) {
			ShelfDB.GUI.Popup.openExternalPopup({
				forceReload: true,
				url: '<?php echo $pdb->RelRoot(); ?>pages/popup-selectsupplier.php',
				afteropen: function(evt) {
					$(evt.target).find("input").first().focus().select();
				},
				afterclose: function(evt) {
				},
				click: function(evt) {
					var fpClicked = $(evt.currentTarget).attr('supplierid');
					if( fpClicked )
					{
						ShelfDB.Parts.editPartFieldData( partId, 'supplierid', fpClicked,
							function(data) {
								if( data && data.success ) {
									// Load store location name and store in database
									$('[name=showSupplier]')
										.attr('value',$(evt.currentTarget).attr('suppliername'))
										.attr('supplierId', fpClicked);

									// Get url for part and update picture
									$.ajax({
										url: '<?php echo $pdb->RelRoot(); ?>lib/json.suppliers.php',
										type: 'POST',
										dataType: 'json',
										data: {
											id: fpClicked,
											partNr: $('[name=showPartNumber]').val()
										}
									}).done(function(data) {
										// Update gui
										if( data ) {
											var imgFile = '<?php echo $pdb->RelRoot(); ?>img/supplier/'+data['pict_fname'];
											$('[name=imgSupplier]').attr({
												src: imgFile,
												'data-other-src': imgFile
											});

											$('[name=showSupplier]').attr('url',data.urlTemplate);
										}
									});
								}
							}
						);
					}
				}
			});
		});

		$('[name=editPrice]').click(function(evt) {
			ShelfDB.GUI.Popup.inputPopUp({
		    header: Lang.get('editPartPrice'),
		    headline: Lang.get('editPartChangePrice'),
		    textPlaceholder: Lang.get('enterPrice'),
		    textDefault: $('[name=showPrice]').val(),
		    ok: function( newPrice ) {
					ShelfDB.Parts.editPartFieldData( partId, 'price', newPrice,
						function(data) {
							if( data && data.success ) {
								// Submit and save new name, then update GUI on success
								$('[name=showPrice]').val(newPrice);
							}
						}
					);
				},
				validatorRules: {
					required: true,
	      	number: true,
	      	min: 0.0
				}
			});
		});

		$('[name=editTotal]').click(function(evt) {
			ShelfDB.GUI.Popup.inputPopUp({
				header: Lang.get('editPartTotal'),
				headline: Lang.get('editPartChangeTotal'),
				textPlaceholder: Lang.get('enterAmount'),
				textDefault: $('[name=showTotal]').val(),
				ok: function( total ) {
					// Submit and save new name, then update GUI on success
					ShelfDB.Parts.editPartFieldData( partId, 'totalstock', total,
						function(data) {
							if( data && data.success ) {
								$('[name=showTotal]').val(total);
							}
						}
					);
				},
				validatorRules: {
					required: true,
					digits: true,
					min: parseInt($('[name=showStock]').val())
				}
			});
		});

		$('[name=addTotal]').click(function(evt) {
			ShelfDB.Parts.incrementTotal(<?php echo $data['partid']; ?>, function(newval) {
				$('[name=showTotal]').val(newval);
				updateButtons();
			});
		});

		$('[name=subTotal]').click(function(evt) {
			ShelfDB.Parts.decrementTotal(<?php echo $data['partid']; ?>, function(newval) {
				$('[name=showTotal]').val(newval);
				updateButtons();
			});
		});

		$('[name=addStock]').click(function(evt) {
			ShelfDB.Parts.incrementStock(<?php echo $data['partid']; ?>, function(newval) {
				$('[name=showStock]').val(newval);
				updateButtons();
			});
		});

		$('[name=subStock]').click(function(evt) {
			ShelfDB.Parts.decrementStock(<?php echo $data['partid']; ?>, function(newval) {
				$('[name=showStock]').val(newval);
				updateButtons();
			});
		});

		$('[name=editStock]').click(function(evt) {
			ShelfDB.GUI.Popup.inputPopUp({
		    header: Lang.get('editPartStock'),
		    headline: Lang.get('editPartChangeStock'),
		    textPlaceholder: Lang.get('enterAmount'),
		    textDefault: $('[name=showStock]').val(),
		    ok: function( stock ) {
					// Submit and save new name, then update GUI on success
					ShelfDB.Parts.editPartFieldData( partId, 'instock', stock,
						function(data) {
							if( data && data.success ) {
								$('[name=showStock]').val(stock);
							}
						}
					);
				},
				validatorRules: {
					required: true,
	      	digits: true,
					number: true,
	      	min: 0,
					max: parseInt($('[name=showTotal]').val())
				}
			});
		});

		$('[name=editMinStock]').click(function(evt) {
			ShelfDB.GUI.Popup.inputPopUp({
		    header: Lang.get('editPartMinStock'),
		    headline: Lang.get('editPartChangeMinStock'),
		    textPlaceholder: Lang.get('enterAmount'),
		    textDefault: $('[name=showMinStock]').val(),
		    ok: function( minstock ) {
					// Submit and save new name, then update GUI on success
					ShelfDB.Parts.editPartFieldData( partId, 'mininstock', minstock,
						function(data) {
							if( data && data.success ) {
								$('[name=showMinStock]').val(minstock);
							}
						}
					);
				},
				validatorRules: {
					required: true,
	      	digits: true,
	      	min: 0
				}
			});
		});

		$('[name=editDescription]').click(function(evt) {
			evt.preventDefault();
			evt.stopPropagation();

			ShelfDB.GUI.Popup.inputMultilinePopUp({
	      header: Lang.get('editPartDescriptionEdit'),
	      headline: Lang.get('editPartDescriptionEditHint'),
	      textPlaceholder: Lang.get('enterDescription'),
	      textDefault: $('[name=showDescription]').text(),
	      ok: function( newdescription ) {
					ShelfDB.Parts.editPartFieldData( partId, 'comment', newdescription,
						function(data) {
							if( data && data.success ) {
								// Submit and save new name, then update GUI on success
								$('[name=showDescription]').text(newdescription);
							}
						}
					);
				}
			});
		});
	});

	$.mobile.pageContainerChangeTasks.push( function() {

		var lastwidth = 9999;
		$(window).on('resize', function() {
			var width = $(".partdetailsblock").width();

			if( width < 360 && lastwidth >= 360 ) {
				$('[name=subTotal],[name=addTotal],[name=subStock],[name=addStock]').parent().hide();
			} else if( width >= 360 && lastwidth < 360) {
				$('[name=subTotal],[name=addTotal],[name=subStock],[name=addStock]').parent().show();
			}

			if( width < 420 && lastwidth >= 420 ) {

			} else if( width >= 420 && lastwidth < 420 ) {

			}
			lastwidth = width;

		});

		// Initial column hide/show
		$(window).triggerHandler('resize');

	});

	</script>

  <div data-role="header" data-position="fixed">
    <h1 uilang="partTitle"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
  </div>
  <div role="main" class="ui-content">
    <div class="partinfo ui-body ui-body-a ui-corner-all ui-shadow">
			<div class="flexBoxTextInputEditControl">
				<div class="flexContainer">
      		<h3 name="showName" style="margin-bottom: 0.1em"><?php echo $name; ?></h3>
					<h5 style="margin-top: 0em">in <?php echo $categoryString; ?></h5>
			  </div>
				<input name="editName" type="button" data-icon="edit" data-iconpos="notext">
				<input name="copyPart" type="button" data-icon="fa-clone" data-iconpos="notext">
				<input name="deletePart" type="button" data-icon="delete" data-iconpos="notext">
			</div>
      <div class="ui-grid-a">
        <div class="ui-block-a partimageblock">
					<div class="partimagewrapper">
						<a id="popuplink" href="#popupimg" data-rel="popup" data-position-to="window">
            	<img class="partimage" data-other-src="<?php echo $part['mainPicFile']; ?>" src="<?php echo $part['mainPicFile']; ?>">
							<?php
								if( \ConfigFile\QRCode::$enable ) { ?>
									<br>
									<img data-other-src="<?php echo $qrImgData = $pdb->Parts()->CreateQRCode($data['partid']); ?>" src="<?php echo $qrImgData; ?>">
								<?php }
							?>
						</a>
					</div>
					<!-- Popup image viewer -->
					<div data-role="popup" id="popupimg" class="photopopup" data-overlay-theme="a" data-corners="false" data-tolerance="30,15">
						<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Schließen</a>
						<img src="" alt="">
					</div>
        </div>
        <div class="ui-block-b partdetailsblock">
          <div class="ui-body ui-body-a ui-corner-all">
            <h4 uilang="storageLocation"></h4>
            <div class="flexBoxTextInputEditControl">
							<input name="showStoreloc" readonly="readonly" type=text value="<?php echo htmlentities($part['storeloc'],ENT_HTML5,'UTF-8'); ?>">
							<input name="editStoreloc" type="button" data-icon="edit" data-iconpos="notext">
						</div>
            <h4 uilang="footprint"></h4>
						<div class="flexBoxTextInputEditControl">
							<a href="#popupimg" data-rel="popup" data-position-to="window">
								<img data-other-src="<?php echo $partFootprintImageFile; ?>" src="<?php echo $partFootprintImageFile ?>" name="imgFootprint" style="max-width: 2.2em; max-height: 2.2em; margin-right: 0.3em">
							</a>
							<input name="showFootprint" readonly="readonly" type=text value="<?php echo htmlentities($part['footprint'],ENT_HTML5,'UTF-8'); ?>">
							<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<h4>
									<span uilang="amountAvailable"></span>&nbsp;
									<a href="#popupInfoTotalInstock" data-rel="popup" data-transition="pop"
									class="ui-btn ui-alt-icon ui-nodisc-icon ui-btn-inline ui-icon-info ui-btn-icon-notext my-tooltip-btn"
									title="Learn more" uilang="moreInfo"></a>
								</h4>
							</div>
							<div data-role="popup" id="popupInfoTotalInstock" class="ui-content" data-theme="a" style="max-width:350px;">
  							<p uilang="helpTotalInStock"></p>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<h4>
									<span uilang="amountStored"></span>&nbsp;
									<a href="#popupInfoInstock" data-rel="popup" data-transition="pop"
									class="ui-btn ui-alt-icon ui-nodisc-icon ui-btn-inline ui-icon-info ui-btn-icon-notext my-tooltip-btn"
									title="Learn more" uilang="moreInfo"></a>
								</h4>
							</div>
							<div data-role="popup" id="popupInfoInstock" class="ui-content" data-theme="a" style="max-width:350px;">
  							<p uilang="helpInStock"></p>
							</div>
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<input name="showTotal" type="text" readonly=readonly value="<?php echo $part['totalstock']; ?>">
									<input name="addTotal" type="button" data-icon="fa-plus-circle" data-iconpos="notext">
									<input name="subTotal" type="button" data-icon="fa-minus-circle" data-iconpos="notext">
									<input name="editTotal" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<input name="showStock" type="text" readonly=readonly value="<?php echo $part['instock']; ?>">
									<input name="addStock" type="button" data-icon="fa-plus-circle" data-iconpos="notext">
									<input name="subStock" type="button" data-icon="fa-minus-circle" data-iconpos="notext">
									<input name="editStock" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<h4 uilang="supplier"></h4>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<h4 uilang="partNumber"></h4>
							</div>
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<a href="#popupimg" data-rel="popup" data-position-to="window">
										<img data-other-src="<?php echo $partSupplierImageFile; ?>" src="<?php echo $partSupplierImageFile ?>" name="imgSupplier"  style="margin-right: 0.3em; max-width: 2.2em; max-height: 2.2em">
									</a>
									<input name="showSupplier" url="<?php echo $url; ?>" supplierId="<?php echo $part['id_supplier']; ?>" type="text" readonly=readonly value="<?php echo $part['supplier_name']; ?>">
									<input name="editSupplier" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<input name="showPartNumber" type="text" readonly=readonly value="<?php echo $part['supplierpartnr']; ?>">
									<input name="editPartNumber" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<h4 uilang="price"></h4>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<h4 uilang="amountLeast"></h4>
							</div>
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<input name="showPrice" type="text" readonly=readonly value="<?php echo $part['price']; ?>">
									<input name="editPrice" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<input name="showMinStock" type="text" readonly=readonly value="<?php echo $part['mininstock']; ?>">
									<input name="editMinStock" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
						</div>
          </div>
        </div>
      </div>
			<div style="ui-body ui-body-a ui-corner-all">
				<div data-enhanced="true" data-role="collapsible" class="ui-collapsible ui-collapsible-inset ui-corner-all ui-collapsible-themed-content">
			    <h4 class="ui-collapsible-heading">
			        <a class="ui-collapsible-heading-toggle ui-btn ui-icon-minus ui-btn-icon-left" href="#">
								<span uilang="description" />
								<span class="ui-collapsible-heading-status"> click to expand contents</span>
			        </a>
							<a name="editDescription" data-enhanced="true" class="ui-collapsible-split-button ui-corner-all ui-btn ui-btn-icon-notext ui-icon-edit" href="#" uilang="edit"></a>
			    </h4>
			    <div class="ui-collapsible-content ui-body-inherit" aria-hidden="false">
			      <p name="showDescription" style="white-space: pre-wrap"><?php
								echo htmlentities(trim($part['comment']),ENT_HTML5,'UTF-8');
							?></p>
			    </div>
				</div>
				<div data-role="collapsible">
			    <h4 uilang="datasheets"></h4>
					<p>TODO: DS</p>
				</div>
				<div name="partPictureListView" data-role="collapsible">
			    <h4 uilang="images"></h4>
					<!-- Part pictures -->
					<?php echo  join("",$partImageHtml); ?>
					<div name="pictureContainer" value="add" style="vertical-align: top; display: inline-block; text-align: center">
						<a name="pictureContainerAddButton" href="#">
						<div class="partinfo partImageListItem" style="font-size: 1em; width: 10em; height: 10em">
							<i class="fa fa-plus" style="font-size: 10em"></i>
						</div>
					</a>
				</div>
				</div>
			</div>
    </div>
		<!-- Popup image viewer -->
		<div data-role="popup" id="popupimg" class="photopopup" data-overlay-theme="a" data-corners="false" data-tolerance="30,15">
			<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Schließen</a>
			<img src="" alt="">
		</div>
  </div>

  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
