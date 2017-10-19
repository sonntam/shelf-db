<?php
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get storelocations
  $storelocations = $pdb->StoreLocation()->GetNonEmpty();
	$storeLocationIds = array_map( function($x) {
		return $x['id'];
	}, $storelocations );
  //$parts = $pdb->Part()->GetSegmentByStoreLocationId($storeLocationIds, 0, 25, "storelocid", "asc", false, null);

	$createListEntryFcn = function( $name, $id, $images ) use(&$pdb) {
		ob_start();
		?>
		<li data-filtertext="<?php echo $name; ?>">

          <a href="<?php echo $pdb->RelRoot(); ?>pages/page-showsearchresults.php?searchMode=storageLocationId&search=<?php echo $id ?>">
						<h2 style="float: left"><?php echo $name; ?></h2>
					</a>
    </li>
		<?php
		return ob_get_clean();
	};

  foreach( $storelocations as &$s ) {
    $name = htmlspecialchars($s['name']);
    //$parts = $pdb->Part()->GetSegmentByStoreLocationId($s['id'],0,15,"totalstock","desc",false,null);

    // Build list of unique images
    /*
    $o = 0;
    $lastPic = null;
    $images = array();
    for( $i = 0; $i < sizeof($parts) && $o < 4; $i++ ) {
      $pic = $parts[$i]['mainPicThumbFile'];
      if( $lastPic != $pic ) {
        $images[] = $parts[$i]['mainPicThumbFile'];
        $o++;
        $lastPic = $pic;
      }
    }
    $images = array_merge($images, array_fill($o,4-$o, $pdb->RelRoot()."img/footprint/default.png") );
		*/
		$s = $createListEntryFcn($name, $s['id'],null);
  }
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=showNonEmptyStoreLocations data-role="page">

	<script>
		$.mobile.pageContainerBeforeShowTasks.push( function(event,ui) {
			console.log("DEBUG: pageTask <?php echo $_SERVER["REQUEST_URI"]; ?>");

			function addNewItem(data) {
				// Add new item
				var elementHtmlDummy = <?php echo json_encode($createListEntryFcn("","","")); ?>;
				var el = $(elementHtmlDummy).prependTo('[name=storelocationList]');
				el.attr('data-filtertext', data.name);
				el.find('h2').text( data.name);
				el.find('a').attr('value',data.id);
				Lang.searchAndReplace();
				el.enhanceWithin();
				$('[name=storelocationList]').listview('refresh');
			}

			$('[name=newStoreLocation]').click( function(evt) {
				ShelfDB.GUI.Popup.openExternalPopup({
					url: '/pages/popup-editstorelocation.php?method=add',
					customEventName: "positiveResponse",
					customEventHandler: function(evt, data) {
						var action = data.buttonresult;
						var newid  = data.id;

						switch( action ) {
							case 'cancel':
								break;
							case 'ok':
								// Reload item
								$.mobile.referencedLoading('show');

								$.ajax({
									url: '/lib/json.storelocations.php?id='+newid,
									cache: false,
									dataType: 'json',
									success: function(data) {
										addNewItem(data);
										$.mobile.referencedLoading('hide');
									},
									error: function() {
										$.mobile.referencedLoading('hide');
									}
								});
								break;
						}
					},
					forceReload: true
				});
			});

			$('[name=storelocationList]').on('click','[name=deleteStoreLocation]', function(evt) {
        var id = $(evt.currentTarget).attr('value');

        if( id )
        {
          var entryEl = $(evt.currentTarget).closest('li');
          var entryName = entryEl.first().find('h2').first().text();
          ShelfDB.GUI.Popup.confirmPopUp({
				    header: Lang.get('editStoreLocationDelete'),
				    text: (Lang.get('editStoreLocationDeleteHint',true))(entryName),
				    confirmButtonText: Lang.get('delete'),
				    confirm: function() {  // Confirmed delete operation
							// TODO: Database action
							$.mobile.referencedLoading('show');
							$.ajax({
								url: '/lib/edit-storelocation.php',
								type: 'POST',
								data: {
									method: 'delete',
									id: id
								},
								dataType: 'json',
								cache: false,
								success: function(data) {
									if( data.success ) {
										entryEl.remove();
		              	$('[data-role="listview"]').listview('refresh');
									}
									$.mobile.referencedLoading('hide');
								},
								error: function() {
									$.mobile.referencedLoading('hide');
								}
							});
            }
          })
        }
      });

      $('[name=storelocationList]').on('click','[name=editStoreLocation]', function(evt) {
				var parent = $(evt.currentTarget).closest('li');
        var id = $(evt.currentTarget).attr('value');

        if( id )
        {
            ShelfDB.GUI.Popup.openExternalPopup({
							url: '/pages/popup-editstorelocation.php?id='+id+'&method=edit',
							customEventName: "positiveResponse",
							customEventHandler: function(evt, data) {
								var action = data.buttonresult;

								switch( action ) {
									case 'cancel':
										break;
									case 'ok':
										// Reload item
										$.mobile.referencedLoading('show');

										$.ajax({
											url: '/lib/json.storelocations.php?id='+id,
											cache: false,
							        dataType: 'json',
											success: function(data) {
												// Rebuild
												parent.attr('data-filtertext', data.name);
												parent.find('h2').text( data.name);
												$('[name=storelocationList]').listview('refresh');
												$.mobile.referencedLoading('hide');
											},
											error: function() {
												$.mobile.referencedLoading('hide');
											}
										});
										break;
								}
							},
							forceReload: true
						});
        }
      });

    });
	</script>

  <div data-role="header" data-position="fixed">
    <h1 uilang="showNonEmptyStoreLocations"></h1>
    <a href="#navPanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<button name="newStoreLocation" class="ui-btn ui-btn-right ui-btn-icon-notext ui-btn-inline ui-icon-fa-plus" uilang="add"></button>
  </div>

  <div role="main" class="ui-content">

		<h3 uilang="storeLocations"></h3>

    <div class="ui-grid-solo" style=" flex: 2; display: flex; flex-flow: column">
      <div class="ui-block-a"><p name="dialogMessage" uilang="popupStoreLocationFilterHint"></p></div>
      <div class="ui-block-a" style="flex: 3; display: flex; flex-flow: column">
        <ul name="storelocationList" data-role="listview" data-inset="true" data-filter="true" uilang="data-filter-placeholder:popupStoreLocationFilterPlaceholder" data-autodividers="true" style="flex: 4; overflow-y: auto; padding: 10px">
          <?php echo join("\n", $storelocations); ?>
        </ul>
      </div>
    </div>
  </div>

  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
</div>
