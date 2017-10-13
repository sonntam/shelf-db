<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	$defaults = array(
		'catid'              => 0,
		'search'             => null,
		'id'                 => null,
		'searchMode' 				 => 'globalString'
	);

	$options = array_replace_recursive( $defaults, $_GET, $_POST );

	switch( $options['searchMode'] ) {
		case 'globalString':
			// Global search
			$searchString = $options['search'];
			$searchUrl    = $pdb->RelRoot().'lib/json.parts.php?globalSearchString='.htmlentities($searchString).'&catid=0';
			break;
		case 'storageLocationId':
			$storelocId = $options['search'];

			if( $sl = $pdb->StoreLocations()->GetById($storelocId) ) {
			} else {
				$sl = array(
					'name' => 'undefined',
					'id' => 0
				);
			}
			$searchString = $sl['name'];
			$searchUrl    = $pdb->RelRoot().'lib/json.parts.php?searchField=storelocid&searchOper=eq&searchString='.$sl['id'].'&catid=0';

			break;
		case 'footprintId':
			$footprintId = $options['search'];

			if( $fp = $pdb->Footprints()->GetById($footprintId) ) {
			} else {
				$fp = array(
					'name' => 'undefined',
					'id' => 0
				);
			}
			$searchString = $fp['name'];
			$searchUrl    = $pdb->RelRoot().'lib/json.parts.php?searchField=footprintid&searchOper=eq&searchString='.$fp['id'].'&catid=0';

			break;
		default:
			return;
	}

	$search     = $options["search"];
	$searchMode = $search && ($search != "");

	$catid      = $options["catid"];
	$catname    = $pdb->Categories()->GetNameById($catid);


	// Filter strings
	$fpFilter = join(';', array_map(function($el){return $el['id'].":".htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Footprints()->GetAll()));
	$slFilter = join(';', array_map(function($el){return $el['id'].":".htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->StoreLocations()->GetAll()));
	$ctFilter = join(';', array_map(function($el){return $el['id'].":".htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Categories()->GetAll()));
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=showsearchresults data-role="page">
	<script>

  var $page;

	// Popup handler
	$.mobile.pageCreateTasks.push( function() {
    $page = $('#showsearchresults');

	  $page.find( ".photopopup" ).on({
      popupbeforeposition: function(evt) {
        var maxHeight = $( window ).height() - 60 + "px";

       	$page.find( ".photopopup img" ).css( "max-height", maxHeight );
    	}
	  });


		$page.find('.ui-content').on('click', 'a', function(evt) {
				// Set data
				var $tn = $(evt.target);

				$page.find( ".photopopup img" ).attr('src', $tn.attr('data-other-src'));

		});
	});

		//$(':mobile-pagecontainer').off("pagecontainerchange");
		//$(':mobile-pagecontainer').on("pagecontainerchange",
		$.mobile.pageContainerChangeTasks.push( function( event, ui ){
        $page = $('#showsearchresults');
				console.log("DEBUG: pagecontainer - change (id = <?php echo $catid; ?>)");
				$page.find("#grido").jqGrid({
					caption: Lang.get('searchTableTitle'),
					url:'<?php echo $searchUrl; ?>',
					editurl: 'edit-part.php',
					autowidth: true,
					shrinkToFit: true,
					datatype: 'json',
					autoencode: true,
					grouping: true,
					groupingView: {
						groupField: ['category_name'],
						groupDataSorted: true,
						groupColumnShow: [false]
					},
					sortable: true,
					cmTemplate: {
						autoResizable: true,
						editable: true
					},
					autoResizing: {
						compact: true
					},
					iconSet: 'fontAwesome',
					rowNum:20,
					rowList: [20,50,100],
					pager:'#listpager',
					toppager:true,
					filterToolbar:true,
					searching: {
						defaultSearch: 'cn'
					},
					inlineEditing: {keys:true, position:"afterSelected"},
					sortname: 'name',
					viewrecords: true,
					sortorder: 'asc',
					viewrecords: false,
					gridComplete: function() {

						var ids = $(this).jqGrid('getDataIDs');
						for( var i = 0; i < ids.length; i++) {
								$(this).jqGrid('setRowData', ids[i], {
									action: '<p>add decrease</p>'
								});
						}

					},
	        colModel: ShelfDB.Parts.getListViewModel({
						footprintFilterString: '<?php echo $fpFilter; ?>',
						storeLocationFilterString: '<?php echo $slFilter; ?>',
						categoryFilterString: '<?php echo $ctFilter; ?>',
						imageFormatter: imageFormatter
					}),
					/*onSelectRow: function(rowid){
						debugger;
						var $self = $(this);
						var savedRow = $self.jqGrid("getGridParam", "savedRow");
						if (savedRow.length > 0) {
							$self.jqGrid("restoreRow", savedRow[0].id);
						}
						//$self.jqGrid("editRow", rowid);
			  	},*/
        });

				$page.find("#grido").jqGrid('navGrid','#listpager',{edit:false, add:true, del:false},{},{},{},{
					multipleSearch: true,
					multipleGroup: false
				});
				// Copy toolbar buttons to top toolbar and hide right side of toppager
				$('#listpager_left').children().clone(true).appendTo('#grido_toppager_left');
				$('#grido_toppager_right').hide();
				$page.find("#grido").jqGrid('navCheckboxAdd', '#' + $page.find("#grido")[0].id + '_toppager_left', { // "#list_toppager_left"
					caption: Lang.get('noGroupingByCategories'),
					position: "first",
					id: "chkHideGroups",
						onChange: function() {
							if($(this).is(":checked")) {
								$page.find("#grido").jqGrid('setGridParam', {
									grouping: false
								}).trigger('reloadGrid');
							} else {
								$page.find("#grido").jqGrid('setGridParam', {
									grouping: true
								}).trigger('reloadGrid',[{page:1}]);
							}
						}
				 });

				// Select current category in tree
				var $tree = $('#categorytree');
				$tree.tree( 'selectNode', $tree.tree('getNodeById', <?php echo $catid; ?>) );

				var lastwidth = 9999;
				$(window).on('resize', function() {
					var width = $page.find("#grido").closest('.ui-content').width();

					if( width < 520 && lastwidth >= 520 ) {
						$page.find("#grido").jqGrid('hideCol',["mininstock"/*,"datasheet"*/]);
					} else if( width >= 520 && lastwidth < 520) {
						$page.find("#grido").jqGrid('showCol',['footprint',"mininstock"/*,"datasheet"*/]);
					}

					if( width < 420 && lastwidth >= 420 ) {
						$page.find("#grido").jqGrid('hideCol',"footprint");
						$("#chkHideGroups_super").hide();
					} else if( width >= 420 && lastwidth < 420 ) {
						$("#chkHideGroups_super").show();
						$page.find("#grido").jqGrid('showCol',"footprint");
					}
					lastwidth = width;

	        $page.find("#grido").jqGrid('setGridWidth', width);
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

				retstr = '<img id="popuptn" style="max-width: 32px; max-height: 32px; height:auto; '
				+ 'width:auto" data-other-src="'+rowObject.mainPicFile+'" src="'+rowObject.mainPicThumbFile+'">'

				retstr = '<a id="popuplink" href="#popupimg" data-rel="popup" data-position-to="window">'
									+ retstr + '</a>';

				return retstr;
			}
	</script>

  <div data-role="header" data-position="fixed">
    <h1 uilang="searchResults"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<?php if( $catid != 0 && $catParentId != 0 ) { ?>
		<a class="ui-btn ui-btn-inline ui-btn-icon-left ui-shadow ui-icon-back" href="page-showparts.php?catid=<?php echo $catParentId; ?>&showSubcategories=1" uilang="upperLevel"></a>
		<?php } ?>
  </div>
  <div role="main" class="ui-content">

			<h3 uilang="searchResultsFor"><?php echo htmlentities($searchString); ?></h3>
			<!-- Bild/Bottomlevel Kategorie/Name/Lagerbestand/Footprint/Lagerort/Datenblätter/+- -->
			<p>
				<table id=grido></table>
				<div id="listpager"></div>
			</p>

		<!-- Popup image viewer -->
		<div data-role="popup" id="popupimg" class="photopopup" data-overlay-theme="a" data-corners="false" data-tolerance="30,15">
			<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Schließen</a>
			<img src="" alt="">
		</div>

  </div>

  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
