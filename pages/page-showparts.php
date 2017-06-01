<?php

	// https://stackoverflow.com/questions/7550304/jqgrid-upload-a-file-in-add-edit-dialog
	// http://www.trirand.com/jqgridwiki/doku.php?id=wiki%3acolmodel_options
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

	$_GET 	+= array("id" => null);

	$catid      = $_GET["catid"];
	$catname    = $pdb->GetCategoryNameFromId($catid);
	$catrecurse = $_GET["catrecurse"] == "1";

	// Create button from category node
	$funCreateButton = function($cat,$recurse) {
		return '<a style="margin: 0pt; padding: 0.4em" class="ui-btn ui-btn-inline ui-corner-all ui-shadow" href="page-showparts.php?catid='.$cat['id'].'&catrecurse='.$recurse.'">'
		 .htmlspecialchars( $cat['name'] )."</a>\n";
	};

	// Get Parent
	$parent  				= $pdb->GetParentCategoryFromId($catid);
	$catParentName 	= $parent['name'];
	$catParentId    = $parent['id'];
	$catHasChildren = (int)( count($pdb->GetCategoryDirectChildrenFromId($catid)) > 0 );
	// Get All parent nodes and create buttons
	$buttons = [];
	if( $catid != 0 )
		$buttons[0] = $funCreateButton(array('id' => $catid, 'name' => $catname), $catHasChildren );

	while($parent['id'] != 0) {
		array_unshift($buttons, $funCreateButton($parent,1));

		$parent  = $pdb->GetParentCategoryFromId($parent['id']);
	}
?>

<script type="text/javascript">
    window.location="index.php#page-showparts?catid=<?php echo $catid; ?>";
</script>

<div id=showparts data-role="page">
	<script>

	// Popup handler
	$( document ).one( "pagecreate", function() {
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

		$(':mobile-pagecontainer').off("pagecontainerchange");
		$(':mobile-pagecontainer').on("pagecontainerchange", function( event, ui ){
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
					$("body").pagecontainer("change","page-showparts.php?catid=" + e.node.id + "&catrecurse=" + Number(e.node.children.length > 0));
					var $tree = $('#categorytree');

					$tree.tree( 'selectNode', $tree.tree('getNodeById', e.node.id) );
				});

				$("#grido").jqGrid({
					caption: 'Teile in Kategorie <?php echo $catname; ?>',
					url:'../lib/json.parts.php?catid=<?php echo $catid; ?>',
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
					sortorder: 'desc',
					viewrecords: false,
					gridComplete: function() {
						debugger;
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
								label: 'Bild',
								index: 'pict_fname_arr',
								fixed: true,
								width: 32,
								sortable: false,
								editable: false,
								align: 'center',
								formatter: imageFormatter
							},
	            {
								name: 'name',
								label: 'Name',
								index: 'name',
								sortable: true,
								align: 'left',
								editrules: {
									required: true
								},
								formatter: 'showlink',
								formatoptions: {
									idName: 'partid'
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
								label: 'Footprint',
								index: 'footprint',
								sortable: false,
								align: 'right',
								edittype: 'select',
								stype: 'select',
								searchoptions: {
									sopt: ["eq","ne"],
									value: ":Any;FE:FedEx;TN:TNT;IN:IN"
								},
								editoptions: {
									value: "<?php echo join(';', array_map(function($el){return $el['id'].":".$el['name'];}, $pdb->GetFootprints())); ?>"
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
									value: ":Any;FE:FedEx;TN:TNT;IN:IN"
								},
								editoptions: {
									value: "<?php echo join(';', array_map(function($el){return $el['id'].":".$el['name'];}, $pdb->GetStorelocations())); ?>"
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
								}
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

				$("#grido").jqGrid('navGrid','#listpager',{edit:false, add:true, del:false});


				// Select current category in tree
				var $tree = $('#categorytree');
				$tree.tree( 'selectNode', $tree.tree('getNodeById', <?php echo $catid; ?>) );

				var lastwidth = 9999;
				$(window).on('resize', function() {
					var width = $("#grido").closest('.ui-content').width();

					if( width < 520 && lastwidth >= 520 ) {
						$("#grido").jqGrid('hideCol',["mininstock","datasheet"]);
					} else if( width >= 520 && lastwidth < 520) {
						$("#grido").jqGrid('showCol',['footprint',"mininstock","datasheet"]);
					}

					if( width < 420 && lastwidth >= 420 ) {
						$("#grido").jqGrid('hideCol',"footprint");
					} else if( width >= 420 && lastwidth < 420 ) {
						$("#grido").jqGrid('showCol',"footprint");
					}
					lastwidth = width;

	        $("#grido").jqGrid('setGridWidth', width);
	      });

				// Initial column hide/show
				$(window).triggerHandler('resize');

			});

			$(':mobile-pagecontainer').off("pagecontainerbeforeload");
			$(':mobile-pagecontainer').on("pagecontainerbeforeload", function(event,ui) {

				console.log("DEBUG: pagecontainer - beforeload (id = <?php echo $catid; ?>)");

				$(window).off('resize');
				//if( $('#subcattree').length ) {
					//$('#subcattree').tree('destroy');
				//}
			});

			function imageFormatter(cellvalue, options, rowObject) {

				var masteridx  = 0;
				var fname      = null;
				var fname_full = null;

				// Find master picture index, if any
				if( rowObject.pict_masterpict_arr ) {
					masteridx = rowObject.pict_masterpict_arr.split(',').indexOf('1')
					masteridx = Math.max(masteridx,0);
				}

				if( rowObject.tn_fname_arr ) {
					var fname_arr = rowObject.tn_fname_arr.split(',');
					masteridx = Math.min(fname_arr.length-1, masteridx);
					fname = fname_arr[masteridx];
				}
				if( rowObject.pict_fname_arr ) {
					var fname_arr = rowObject.pict_fname_arr.split(',');
					masteridx = Math.min(fname_arr.length-1, masteridx);
					if( !fname ) {
						fname = fname_arr[masteridx];
					}
					fname_full = fname_arr[masteridx];
				}

				var retstr = '';
				if( fname ) {
					retstr += '<img id="popuptn" style="max-width: 32px; max-height: 32px; height:auto; '
					+ 'width:auto" data-other-src="/img/parts/'+ fname_full +'" src="/img/parts/' + fname + '">';
					if( fname_full ) {
						retstr = '<a id="popuplink" href="#popupimg" data-rel="popup" data-position-to="window">'
						+ retstr + '</a>';
					}
				}
				return retstr;
			}
	</script>

  <div data-role="header">
    <h1>Kategorie <?php echo $catname; ?></a></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<?php if( $catid != 0 && $catParentId != 0 ) { ?>
		<a class="ui-btn ui-btn-inline ui-btn-icon-left ui-shadow ui-icon-back" href="page-showparts.php?catid=<?php echo $catParentId; ?>&catrecurse=1">Ebene höher</a>
		<?php } ?>
  </div>
  <div role="main" class="ui-content">
		<?php
			echo join("<i class='fa fa-arrow-right'></i>",$buttons);
			if( $catrecurse )
			{
		?>

			<h3>Unterkategorien</h3>
			<div id="subcattree" data-url="categorytree.json.php?catid=<?php echo $catid; ?>&withparent=<?php echo ($catid == 0 ? 0 : 1) ?>"></div>
		<?php
			}
		?>

			<h3>Teile</h3>
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
