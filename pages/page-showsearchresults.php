<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

	$_GET 	+= array("id" => null);
	$_GET   += array("search" => null);
	$_GET		+= array("catid" => 0);

	$search     = $_GET["search"];
	$searchMode = $search && ($search != "");

	$catid      = $_GET["catid"];
	$catname    = $pdb->Categories()->GetNameById($catid);
	$catrecurse = $_GET["catrecurse"] == "1";


	// FIlter strings
	$fpFilter = join(';', array_map(function($el){return $el['id'].":".htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->Footprints()->GetAll()));
	$slFilter = join(';', array_map(function($el){return $el['id'].":".htmlspecialchars($el['name'],ENT_QUOTES);}, $pdb->StoreLocations()->GetAll()));
?>

<script type="text/javascript">
    window.location="index.php#page-showparts?catid=<?php echo $catid; ?>";
</script>

<div id=showsearchresults data-role="page">
	<script>

	pageHookClear();

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
					url:'../lib/json.parts.php?globalSearchString=<?php echo htmlentities($search); ?>&catid=<?php echo $catid; ?>',
					editurl: 'edit-part.php',
					autowidth: true,
					shrinkToFit: true,
					datatype: 'json',
					autoencode: true,
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
	        colModel: [
							{
								name: 'image',
								label: Lang.get('image'),
								index: 'mainpicfile',
								fixed: true,
								width: 32+5 /*2+2+1 padding + border*/,
								sortable: false,
								editable: false,
								align: 'center',
								formatter: imageFormatter,
								search: false,
							},
	            {
								name: 'name',
								label: Lang.get('name'),
								index: 'name',
								sortable: true,
								align: 'left',
								editrules: {
									required: true
								},
								formatter: 'showlink',
								formatoptions: {
									idName: 'partid',
									baseLinkUrl: '/pages/page-showpartdetail.php',
								},
								width: 40
						 	},
	            {
								name: 'instock',
								label: 'Vorh.',
								index: 'instock',
								sortable: true,
								align: 'right',
								template: 'integer',
								width: 40,
								fixed: true,
								editable: function(opts) {
									return (opts.mode == "edit" ? false : true);
								},
								editrules: {
									integer: true,
									minValue: 0,
									edithidden: true
								}
							},
							{
								name: 'mininstock',
								label: 'Min.',
								index: 'mininstock',
								sortable: true,
								align: 'right',
								template: 'integer',
								width: 40,
								fixed: true,
								editrules: {
									required: true,
									minValue: 0,
									integer: true
								}
							},
							{
								name: 'footprint',
								label: Lang.get('footprint'),
								index: 'footprint',
								sortable: true,
								align: 'right',
								edittype: 'select',
								stype: 'select',
								searchoptions: {
									sopt: ["eq","ne"],
									value: ":Any;<?php echo $fpFilter; ?>"
								},
								editoptions: {
									value: "<?php echo $fpFilter; ?>"
								},
								width: 10
							},
							{
								name: 'storeloc',
								label: 'Lagerort',
								index: 'storelocid',
								sortable: true,
								align: 'right',
								edittype: 'select',
								formatter: 'select',
								stype: 'select',
								searchoptions: {
									sopt: ["eq","ne"],
									value: ":Any;<?php echo $slFilter; ?>"
								},
								editoptions: {
									value: "<?php echo $slFilter; ?>"
								},
								width: 10,
							},
							{
								name: 'datasheet',
								label: 'Datenblätter',
								template: 'datasheet',
								sortable: false,
								align: 'left',
								width: 80,
								fixed: true,
								editable: function(opts) {
									return (opts.mode == "edit" ? false : true);
								},
								search: false,
							},
							{
								name: 'actions',
								label: 'Aktionen',
								template: 'actions',
								align: 'center',
								formatter: 'actions',
								formatoptions: {
									keys: true
								}
							}
	        ],
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

				$page.find("#grido").jqGrid('navGrid','#listpager',{edit:false, add:true, del:false});


				// Select current category in tree
				var $tree = $('#categorytree');
				$tree.tree( 'selectNode', $tree.tree('getNodeById', <?php echo $catid; ?>) );

				var lastwidth = 9999;
				$(window).on('resize', function() {
					var width = $page.find("#grido").closest('.ui-content').width();

					if( width < 520 && lastwidth >= 520 ) {
						$page.find("#grido").jqGrid('hideCol',["mininstock","datasheet"]);
					} else if( width >= 520 && lastwidth < 520) {
						$page.find("#grido").jqGrid('showCol',['footprint',"mininstock","datasheet"]);
					}

					if( width < 420 && lastwidth >= 420 ) {
						$page.find("#grido").jqGrid('hideCol',"footprint");
					} else if( width >= 420 && lastwidth < 420 ) {
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
		<a class="ui-btn ui-btn-inline ui-btn-icon-left ui-shadow ui-icon-back" href="page-showparts.php?catid=<?php echo $catParentId; ?>&catrecurse=1">Ebene höher</a>
		<?php } ?>
  </div>
  <div role="main" class="ui-content">

			<h3 uilang="searchResultsFor"><?php echo htmlentities($search); ?></h3>
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
