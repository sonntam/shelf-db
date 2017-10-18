<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	// Handle page arguments
	$defaults = array(
		'catid'              => 0,
		'search'             => null,
		'id'                 => null
	);

	$options = array_replace_recursive( $defaults, $_GET, $_POST );

	$search     = $options['search'];
	$searchMode = $search && ($search != '');

	$catid             = $options['catid'];
	$catname           = $pdb->Category()->GetNameById($catid);
	$showSubcategories = $options['showSubcategories'] == '1';

	// Create button from category node
	$funCreateButton = function($cat,$recurse) {
		return '<a style="margin: 0pt; padding: 0.4em" class="ui-btn ui-btn-inline ui-corner-all ui-shadow" href="page-showparts.php?catid='.$cat['id'].'&showSubcategories='.$recurse.'">'
		 .htmlspecialchars( $cat['name'] )."</a>\n";
	};

	// Get Parent
	$parent  				= $pdb->Category()->GetParentFromId($catid);
	$catParentName 	= $parent['name'];
	$catParentId    = $parent['id'];
	$catHasChildren = (int)( count($pdb->Category()->GetDirectChildrenFromId($catid)) > 0 );
	// Get All parent nodes and create buttons
	$buttons = [];
	if( $catid != 0 )
		$buttons[0] = $funCreateButton(array('id' => $catid, 'name' => $catname), $catHasChildren );

	while($parent['id'] != 0) {
		array_unshift($buttons, $funCreateButton($parent,1));

		$parent  = $pdb->Category()->GetParentFromId($parent['id']);
	}

	// Filter strings
	$fpFilter = join(';', array_map(function($el){return $el['id'].':'.htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Footprint()->GetAll()));
	$slFilter = join(';', array_map(function($el){return $el['id'].':'.htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->StoreLocation()->GetAll()));
	$ctFilter = join(';', array_map(function($el){return $el['id'].':'.htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Category()->GetAll()));
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=showparts data-role="page">
	<script>
	console.log("DEBUG: page-showparts executing...")

		$.mobile.pageContainerChangeTasks.push( function( event, ui ){
				console.log("DEBUG: pagecontainer - change (id = <?php echo $catid; ?>)");

				var $subtree = $('#subcattree');
				$subtree.tree();

				// Tree callback
				$subtree.bind('tree.init', function(e) {
					$subtree.tree('openNode',$subtree.tree('getNodeById',<?php echo $catid; ?>));
				});
				$subtree.bind('tree.click', function(e) {
					// e.node.name - Name string
					// e.node.id   - ID string
					$('body').pagecontainer('change','page-showparts.php?catid=' + e.node.id + '&showSubcategories=' + Number(e.node.children.length > 0));
					var $tree = $('#categorytree');

					$tree.tree( 'selectNode', $tree.tree('getNodeById', e.node.id) );
				});

				// Part list
				ShelfDB.GUI.Part.PartList.setup({
					listSelector: '#partList',
					caption: Lang.get('partsInCategoryNameHeader', true)('<?php echo $catname; ?>'),
					filterParameters: {
						catid: <?php echo $catid; ?>,
					},
					enableGrouping: <?php echo ($catHasChildren ? 'true' : 'false');?>,
					showGroupingSwitch: <?php echo ($catHasChildren ? 'true' : 'false');?>,
					groupingSwitch: {
						caption: Lang.get('hideSubcategories'),
						id: 'chkHideSubcategories'
					},
					pagerSelector: '#partListPager',
					footprintFilterString: '<?php echo $fpFilter; ?>',
					storeLocationFilterString: '<?php echo $slFilter; ?>',
					categoryFilterString: '<?php echo $ctFilter; ?>',
				});

				// Select current category in tree
				var $tree = $('#navCategorytree');
				$tree.tree( 'selectNode', $tree.tree('getNodeById', <?php echo $catid; ?>) );

				var lastwidth = 9999;
				$(window).on('resize', function() {
					var width = $("#partList").closest('.ui-content').width();

					if( width < 520 && lastwidth >= 520 ) {
						$('#partList').jqGrid('hideCol',['mininstock'/*,'datasheet'*/]);
					} else if( width >= 520 && lastwidth < 520) {
						$('#partList').jqGrid('showCol',['footprint','mininstock'/*,'datasheet'*/]);
					}

					if( width < 420 && lastwidth >= 420 ) {
						$('#partList').jqGrid('hideCol','footprint');
						$('#chkHideSubcategories_super').hide();
					} else if( width >= 420 && lastwidth < 420 ) {
						$('#chkHideSubcategories_super').show();
						$('#partList').jqGrid('showCol','footprint');
					}
					lastwidth = width;

	        $('#partList').jqGrid('setGridWidth', width);
	      });

				// Initial column hide/show
				$(window).triggerHandler('resize');

			});

			//$(':mobile-pagecontainer').off("pagecontainerbeforeload");
			//$(':mobile-pagecontainer').on("pagecontainerbeforeload",
			$.mobile.pageContainerBeforeLoadTasks.push( function(event,ui) {

				console.log("DEBUG: pagecontainer - beforeload (id = <?php echo $catid; ?>)");

				$(window).off('resize');
				//if( $('#subcattree').length ) {
					//$('#subcattree').tree('destroy');
				//}
			});

			function imageFormatter(cellvalue, options, rowObject) {

				var retstr = '';

				retstr = '<img style="max-width: 32px; max-height: 32px; height:auto; '
				+ 'width:auto" data-other-src="'+rowObject.mainPicFile+'" src="'+rowObject.mainPicThumbFile+'">'

				retstr = '<a href="#imgViewer" data-rel="popup" data-position-to="window">'
									+ retstr + '</a>';

				return retstr;
			}
	</script>

  <div data-role="header" data-position="fixed">
    <h1 uilang=":categoryNameHeader:"><?php echo $catname; ?></a></h1>
    <a href="#navPanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<?php if( $catid != 0 && $catParentId != 0 ) { ?>
		<a class="ui-btn ui-btn-inline ui-btn-icon-left ui-shadow ui-icon-back" href="page-showparts.php?catid=<?php echo $catParentId; ?>&showSubcategories=1" uilang="upperLevel"></a>
		<?php } ?>
  </div>
  <div role="main" class="ui-content">
		<?php
			echo join("<i class='fa fa-arrow-right'></i>",$buttons);
			if( $showSubcategories )
			{
		?>

			<h3 uilang="subCategories"></h3>
			<div id="subcattree" data-url="<?php echo $pdb->RelRoot(); ?>lib/json.categorytree.php?catid=<?php echo $catid; ?>&withparent=<?php echo ($catid == 0 ? 0 : 1) ?>"></div>
		<?php
			}
		?>

			<h3 uilang="parts"></h3>
			<!-- Bild/Bottomlevel Kategorie/Name/Lagerbestand/Footprint/Lagerort/Datenblätter/+- -->
			<p>
				<table id="partList"></table>
				<div id="partListPager"></div>
			</p>

		<!-- Popup image viewer -->
		<div data-role="popup" id="popupimg" class="photopopup" data-overlay-theme="a" data-corners="false" data-tolerance="30,15">
			<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right" uilang="close"></a>
			<img src="" alt="">
		</div>

  </div>

  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
