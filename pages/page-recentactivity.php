<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	// Handle page arguments
	$defaults = array(
		'maxCount'           => 10
	);

	$options = array_replace_recursive( $defaults, $_GET, $_POST );

  $history = $pdb->History()->GetRecent($options['maxCount']);
  $history = $pdb->History()->PrintSimpleHistoryData($history);
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=showparts data-role="page">
	<script>
	</script>

  <div data-role="header" data-position="fixed">
    <h1>Recent activity</h1>
    <a href="#navPanel" class="ui-btn"><i class="fa fa-bars"></i></a>
  </div>
  <div role="main" class="ui-content">
    <div style="font-size: 10pt">
    <?php echo $history; ?>
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
