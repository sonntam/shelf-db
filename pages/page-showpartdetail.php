<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

	$_GET 	+= array("partid" => null);

  if( $_GET["partid"] != null )
    $part = $pdb->GetPartDetailById($_GET["partid"]);

	$name = htmlspecialchars($part['name']);
?>

<script type="text/javascript">
    window.location="/index.php";
</script>

<div id=showpartdetail data-role="page">
	<script>

	pageHookClear();

	// Popup handler
	$.mobile.pageCreateTasks.push( function() {
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
			  // textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel)
			 debugger;
			inputPopUp(
				Lang.get('editPartNewName'),
				Lang.get('editPartChangeName'),
				"",
				Lang.get('ok'),
				"",
				Lang.get('enterName'),
				$('.partDetail > h3').first().text(),
				function( newname ) {
					// TODO Submit and save new name, then update GUI on success
				}
			);
		});

		$('[name=editStoreloc]').click(function(evt) {
			openExternalPopup('/pages/popup-storelocselect.php',
			function(evt) {
				$(evt.target).find("input").first().focus().select();
			},
			function(evt) {
				//debugger;
				//alert(JSON.stringify(evt));
			},
			function(evt) {
				var storeClicked = $(evt.currentTarget).attr('storeid');
				if( storeClicked )
				{
					//evt.preventDefault();
					// Load store location name and store in database
					$('[name=showStoreloc]').attr('value',$(evt.currentTarget).attr('storename'));
				}
			});
		});

		$('[name=editFootprint]').click(function(evt) {
			openExternalPopup('/pages/popup-footprintselect.php',
			function(evt) {
				$(evt.target).find("input").first().focus().select();
			},
			function(evt) {
				//debugger;
				//alert(JSON.stringify(evt));
			},
			function(evt) {
				var fpClicked = $(evt.currentTarget).attr('footprintid');
				if( fpClicked )
				{

					//evt.preventDefault();
					// Load store location name and store in database
					$('[name=showFootprint]').attr('value',$(evt.currentTarget).attr('footprintname'));
				}
			});
		});
	});

	</script>

  <div data-role="header">
    <h1 uilang="partTitle"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
  </div>
  <div role="main" class="ui-content">
    <div class="partinfo ui-body ui-body-a ui-corner-all">
			<div class="partDetail">
      	<h3><?php echo $name; ?></h3>
				<input name="editName" type="button" data-icon="edit" data-iconpos="notext">
			</div>
      <div class="ui-grid-a">
        <div class="ui-block-a">
            <img class="partimg" src="/img/parts/<?php echo explode(',',$part['pict_fname_arr'])[0]; ?>">
        </div>
        <div class="ui-block-b">
          <div class="ui-body ui-body-a ui-corner-all">
            <h4 uilang="storageLocation"></h4>
            <div class="partDetail">
							<input name="showStoreloc" readonly="readonly" type=text value="<?php echo htmlentities($part['storeloc'],ENT_HTML5,'UTF-8'); ?>">
							<input name="editStoreloc" type="button" data-icon="edit" data-iconpos="notext">
						</div>
            <h4 uilang="footprint"></h4>
						<div class="partDetail">
							<input name="showFootprint" readonly="readonly" type=text value="<?php echo htmlentities($part['footprint'],ENT_HTML5,'UTF-8'); ?>">
							<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
						</div>
						<div class="ui-grid-a">
							<div class="ui-block-a">
								<h4>Menge vorhanden</h4>
								<div class="partDetail">
									<input type="text" readonly=readonly value="<?php echo $part['totalstock']; ?>">
									<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
									<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
							<div class="ui-block-b">
								<h4>Menge eingelagert</h4>
								<div class="partDetail">
									<input type="text" readonly=readonly value="<?php echo $part['instock']; ?>">
									<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
									<input name="editFootprint" type="button" data-icon="edit" data-iconpos="notext">
								</div>
							</div>
						</div>
          </div>
        </div>
      </div>
      <div class="ui-body ui-body-a ui-corner-all">
        <h4 uilang="description"></h4>
        <p>
					<?php echo nl2br(htmlentities($part['comment'],ENT_HTML5,'UTF-8')); ?>
				</p>
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
