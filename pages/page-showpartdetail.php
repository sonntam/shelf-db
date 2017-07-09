<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	$_GET 	+= array("partid" => null);

  if( $_GET["partid"] != null )
	{
    $part = $pdb->Parts()->GetDetailsById($_GET["partid"]);
		$name = $part['name'];
		$partFootprintImageFile = joinPaths( $pdb->RelRoot(), 'img/footprint', $part['f_pict_fname']);
		$partSupplierImageFile = joinPaths( $pdb->RelRoot(), 'img/supplier', $part['su_pict_fname']);
	}

?>

<script type="text/javascript">
    window.location="/index.php";
</script>

<div id=showpartdetail data-role="page">
	<script>

	pageHookClear();

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

		$('[name=showSupplier]').click(function(evt) {
			var url = $(this).attr('url');
			if( url )
				window.open(url,'_blank');
		});

		$('[name=editPartNumber]').click(function(evt) {
			inputPopUp({
					header: Lang.get('editPartPartNumber'),
					headline: Lang.get('editPartChangePartNumber'),
					textPlaceholder: Lang.get('enterPartNumber'),
					textDefault: $('[name=showPartNumber]').val(),
					ok: function( newnumber ) {
						$('[name=showPartNumber]').val(newnumber);
						// Get url for part and update picture
						//
						$.mobile.referencedLoading('show');
						$.ajax({
							url: '<?php echo $pdb->RelRoot(); ?>lib/json.suppliers.php',
							type: 'POST',
							dataType: 'json',
							data: {
								id: $('[name=showSupplier]').attr('supplierId'),
								partNr: newnumber
							}
						}).done(function(data) {
							// Update gui
							if( data ) {
								$('[name=showSupplier]').attr('url',data.urlTemplate);
							}
							$.mobile.referencedLoading('hide');
						});
					}
			});
		});

		$('[name=editName]').click(function(evt) {
			inputPopUp({
		    header: Lang.get('editPartNewName'),
		    headline: Lang.get('editPartChangeName'),
		    textPlaceholder: Lang.get('enterName'),
		    textDefault: $('[name=showName]').text(),
		    ok: function( newname ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showName]').text(newname);
				}
			});
		});

		$('[name=deletePart]').click(function(evt) {
			confirmPopUp({
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
			openExternalPopup({
				forceReload: true,
				url: '/pages/popup-selectstorelocation.php',
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
						//evt.preventDefault();
						// Load store location name and store in database
						$('[name=showStoreloc]').attr('value',$(evt.currentTarget).attr('storename'));
					}
				}
			});
		});

		$('[name=editFootprint]').click(function(evt) {
			openExternalPopup({
				forceReload: true,
				url: '/pages/popup-selectfootprint.php',
				afteropen: function(evt) {
					$(evt.target).find("input").first().focus().select();
				},
				afterclose: function(evt) {
				},
				click: function(evt) {
					var fpClicked = $(evt.currentTarget).attr('footprintid');
					if( fpClicked )
					{
						// Load store location name and store in database
						$('[name=showFootprint]').attr('value',$(evt.currentTarget).attr('footprintname'));
						// Update picture
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
			});
		});

		$('[name=editSupplier]').click(function(evt) {
			openExternalPopup({
				forceReload: true,
				url: '/pages/popup-selectsupplier.php',
				afteropen: function(evt) {
					$(evt.target).find("input").first().focus().select();
				},
				afterclose: function(evt) {
				},
				click: function(evt) {
					var fpClicked = $(evt.currentTarget).attr('supplierid');
					if( fpClicked )
					{
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
			});
		});

		$('[name=editPrice]').click(function(evt) {
			inputPopUp({
		    header: Lang.get('editPartPrice'),
		    headline: Lang.get('editPartChangePrice'),
		    textPlaceholder: Lang.get('enterPrice'),
		    textDefault: $('[name=showPrice]').val(),
		    ok: function( newPrice ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showPrice]').val(newPrice);
				},
				validatorRules: {
					required: true,
	      	number: true,
	      	min: 0.0
				}
			});
		});

		$('[name=editTotal]').click(function(evt) {
			inputPopUp({
				header: Lang.get('editPartTotal'),
				headline: Lang.get('editPartChangeTotal'),
				textPlaceholder: Lang.get('enterAmount'),
				textDefault: $('[name=showTotal]').val(),
				ok: function( minstock ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showTotal]').val(minstock);
				},
				validatorRules: {
					required: true,
					digits: true,
					min: 0
				}
			});
		});

		$('[name=addTotal]').click(function(evt) {
			debugger;
		});

		$('[name=subTotal]').click(function(evt) {
			debugger;
		});

		$('[name=editStock]').click(function(evt) {
			inputPopUp({
		    header: Lang.get('editPartStock'),
		    headline: Lang.get('editPartChangeStock'),
		    textPlaceholder: Lang.get('enterAmount'),
		    textDefault: $('[name=showStock]').val(),
		    ok: function( minstock ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showStock]').val(minstock);
				},
				validatorRules: {
					required: true,
	      	digits: true,
	      	min: 0
				}
			});
		});

		$('[name=editMinStock]').click(function(evt) {
			inputPopUp({
		    header: Lang.get('editPartMinStock'),
		    headline: Lang.get('editPartChangeMinStock'),
		    textPlaceholder: Lang.get('enterAmount'),
		    textDefault: $('[name=showMinStock]').val(),
		    ok: function( minstock ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showMinStock]').val(minstock);
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

			inputMultilinePopUp({
	      header: Lang.get('editPartDescriptionEdit'),
	      headline: Lang.get('editPartDescriptionEditHint'),
	      textPlaceholder: Lang.get('enterDescription'),
	      textDefault: $('[name=showDescription]').text(),
	      ok: function( newdescription ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showDescription]').text(newdescription);
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

  <div data-role="header">
    <h1 uilang="partTitle"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
  </div>
  <div role="main" class="ui-content">
    <div class="partinfo ui-body ui-body-a ui-corner-all ui-shadow">
			<div class="flexBoxTextInputEditControl">
      	<h3 name="showName"><?php echo $name; ?></h3>
				<input name="editName" type="button" data-icon="edit" data-iconpos="notext">
				<input name="copyPart" type="button" data-icon="fa-clone" data-iconpos="notext">
				<input name="deletePart" type="button" data-icon="delete" data-iconpos="notext">
			</div>
      <div class="ui-grid-a">
        <div class="ui-block-a partimageblock">
					<div class="partimagewrapper">
						<a id="popuplink" href="#popupimg" data-rel="popup" data-position-to="window">
            	<img class="partimage" data-other-src="<?php echo $part['mainPicFile']; ?>" src="<?php echo $part['mainPicFile']; ?>">
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
									<input name="showSupplier" supplierId="<?php echo $part['id_supplier']; ?>" type="text" readonly=readonly value="<?php echo $part['supplier_name']; ?>">
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
				<div data-role="collapsible">
			    <h4 uilang="images"></h4>
					<p>TODO: IMG</p>
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
