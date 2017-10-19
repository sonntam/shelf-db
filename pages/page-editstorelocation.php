<?php
	require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get storelocations
  $storelocations = $pdb->StoreLocation()->GetAll();

	$createListEntryFcn = function( $name, $id ) {
		ob_start();
		?>
		<li data-icon="false" data-filtertext="<?php echo $name; ?>">

          <h2 style="float: left"><?php echo $name; ?></h2>
          <div style="margin: 0 0; float: right; width: 20.5em" position=relative align=right data-role='controlgroup' data-mini='true' data-type='horizontal'>
            <a href='#' name="deleteStoreLocation" value="<?php echo $id; ?>" class='ui-btn ui-icon-delete ui-btn-icon-top' uilang='delete'></a>
            <a href='#' name="editStoreLocation" value="<?php echo $id; ?>" class='ui-btn ui-icon-edit ui-btn-icon-top' uilang='edit'></a>
          </div>
    </li>
		<?php
		return ob_get_clean();
	};

  foreach( $storelocations as &$s ) {
    $name = htmlspecialchars($s['name']);
		$s = $createListEntryFcn($name, $s['id']);
  }
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=editstorelocations data-role="page">

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
					url: ShelfDB.Core.basePath+'pages/popup-editstorelocation.php?method=add',
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
									url: ShelfDB.Core.basePath+'lib/json.storelocations.php?id='+newid,
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
								url: ShelfDB.Core.basePath+'lib/edit-storelocation.php',
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
							url: ShelfDB.Core.basePath+'pages/popup-editstorelocation.php?id='+id+'&method=edit',
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
											url: ShelfDB.Core.basePath+'lib/json.storelocations.php?id='+id,
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
    <h1 uilang="editStoreLocations"></h1>
    <a href="#navPanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<button name="newStoreLocation" class="ui-btn ui-btn-right ui-btn-icon-notext ui-btn-inline ui-icon-fa-plus" uilang="add"></button>
  </div>

  <div role="main" class="ui-content">

		<h3 uilang="storageLocations"></h3>

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
