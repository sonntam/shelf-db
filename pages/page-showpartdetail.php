<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

	$_GET 	+= array("partid" => null);

  if( $_GET["partid"] != null )
    $part = $pdb->GetPartDetailById($_GET["partid"]);
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
	});

	</script>

  <div data-role="header">
    <h1><?php echo $part['name']; ?></a></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
  </div>
  <div role="main" class="ui-content">
    <div class="partinfo ui-body ui-body-a ui-corner-all">
      <h3><?php echo $part['name']; ?></h3>
      <div class="ui-grid-a">
        <div class="ui-block-a">
            <img class="partimg" src="/img/parts/<?php echo explode(',',$part['pict_fname_arr'])[0]; ?>">
        </div>
        <div class="ui-block-b">
          <div class="ui-body ui-body-a ui-corner-all">
            <h4 uilang="storageLocation"></h4>
            <?php echo htmlentities($part['storeloc'],ENT_HTML5,'UTF-8'); ?>

            <h4 uilang="footprint"></h4>
            <?php echo htmlentities($part['footprint'],ENT_HTML5,'UTF-8'); ?>
          </div>
        </div>
      </div>
      <div class="ui-body ui-body-a ui-corner-all">
        <h4 uilang="description"></h4>
        <?php echo nl2br(htmlentities($part['comment'],ENT_HTML5,'UTF-8')); ?>
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
