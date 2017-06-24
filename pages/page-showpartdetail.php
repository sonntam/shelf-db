<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

	$_GET 	+= array("partid" => null);

  if( $_GET["partid"] != null )
	{
    $part = $pdb->Parts()->GetDetailsById($_GET["partid"]);
		$name = $part['name'];
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

		$('[name=editName]').click(function(evt) {
			// inputPopUp(header, headline, message, confirmbtntext,
			// 	textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel)
			inputPopUp(
				Lang.get('editPartNewName'),
				Lang.get('editPartChangeName'),
				"",
				Lang.get('ok'),
				"",
				Lang.get('enterName'),
				$('[name=showName]').text(),
				function( newname ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showName]').text(newname);
				}
			);
		});

		$('[name=deletePart]').click(function(evt) {
			// inputPopUp(header, headline, message, confirmbtntext,
			// 	textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel)
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
				url: '/pages/popup-storelocselect.php',
				afteropen: function(evt) {
					$(evt.target).find("input").first().focus().select();
				},
				afterclose: function(evt) {
					//debugger;
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
				url: '/pages/popup-footprintselect.php',
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
					}
				}
			});
		});

		$('[name=editDescription]').click(function(evt) {
			evt.preventDefault();
			evt.stopPropagation();

			inputMultilinePopUp(
				Lang.get('editPartDescriptionEdit'),
				Lang.get('editPartDescriptionEditHint'),
				"",
				Lang.get('ok'),
				"",
				Lang.get('enterDescription'),
				$('[name=showDescription]').text(),
				function( newdescription ) {
					// TODO Submit and save new name, then update GUI on success
					$('[name=showDescription]').text(newdescription);
				}
			);
		});
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
							<input name="showFootprint" readonly="readonly" type=text value="<?php echo htmlentities($part['footprint'],ENT_HTML5,'UTF-8'); ?>">
							<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a" style="padding-right: 0.5em">
								<h4>
									Menge vorhanden&nbsp;
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
									Menge eingelagert&nbsp;
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
								</div>
							</div>
							<div class="ui-block-b" style="padding-left: 0.5em">
								<div class="flexBoxTextInputEditControl">
									<input name="showStock" type="text" readonly=readonly value="<?php echo $part['instock']; ?>">
									<input name="addStock" type="button" data-icon="fa-plus-circle" data-iconpos="notext">
									<input name="subStock" type="button" data-icon="fa-minus-circle" data-iconpos="notext">
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
									<input name="showSupplier" type="text" readonly=readonly value="<?php echo $part['supplier_name']; ?>">
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
