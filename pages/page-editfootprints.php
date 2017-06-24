<?php
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  // Get footprints
  $footprints = $pdb->Footprints()->GetAll();

	$createListEntryFcn = function( $name, $pictureFilename, $id ) {
		ob_start();
		?>
		<li data-icon="false" data-filtertext="<?php echo $name; ?>">
      <div class="ui-grid-a">
        <div class="ui-block-a" style="max-width: 7em">
          <img style="max-width: 5em; max-height: 5em" src='/img/footprint/<?php echo $pictureFilename; ?>'>
        </div>
        <div class="ui-block-b">
          <h2><?php echo $name; ?></h2>
        </div>
        <div class="ui-block-c" style="max-width: 22em">
          <div style="margin: 0 0; width: 20.5em" position=relative align=right data-role='controlgroup' data-mini='true' data-type='horizontal'>
            <a href='#' name="deleteFootprint" value="<?php echo $id; ?>" class='ui-btn ui-corner-all ui-icon-delete ui-btn-icon-top' uilang='delete'></a>
            <a href='#' name="editFootprint" value="<?php echo $id; ?>" class='ui-btn ui-corner-all ui-icon-edit ui-btn-icon-top' uilang='edit'></a>
            <a href='#' name="copyFootprint" value="<?php echo $id; ?>" class='ui-btn ui-corner-all ui-icon-fa-copy ui-btn-icon-top' uilang='copy'></a>
          </div>
        </div>
      </div>
    </li>
		<?php
		return ob_get_clean();
	};

  foreach( $footprints as &$f ) {
    $name = htmlspecialchars($f['name']);
		$f = $createListEntryFcn($name, $f['pict_fname'], $f['id']);
  }
?>

<script type="text/javascript">
    window.location="index.php#page-showparts?catid=1";
</script>

<div id=editfootprints data-role="page">

	<script>
		pageHookClear();

		$.mobile.pageContainerBeforeShowTasks.push( function(event,ui) {
			console.log("DEBUG: pageTask");

			function addNewItem(data) {
				// Add new item
				var elementHtmlDummy = <?php echo json_encode($createListEntryFcn("","","")); ?>;
				var el = $(elementHtmlDummy).prependTo('#footprintList');
				el.attr('data-filtertext', data.name);
				el.find('h2').text( data.name);
				el.find('img').attr( 'src', '/img/footprint/' + data.pict_fname);
				el.find('a').attr('value',data.id);
				Lang.searchAndReplace();
				el.enhanceWithin();
				$('#footprintList').listview('refresh');
			}

			$('#newFootprint').click( function(evt) {
				openExternalPopup({
					url: '/pages/popup-editfootprint.php?method=add',
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
									url: '/lib/json.footprints.php?id='+newid,
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

      $('#footprintList').on('click','[name=deleteFootprint]', function(evt) {
        var id = $(evt.currentTarget).attr('value');

        if( id )
        {
          var entryEl = $(evt.currentTarget).closest('li');
          var entryName = entryEl.first().find('h2').first().text();
          confirmPopUp({
				    header: Lang.get('editFootprintDelete'),
				    text: (Lang.get('editFootprintDeleteHint'))(entryName),
				    confirmButtonText: Lang.get('delete'),
				    confirm: function() {  // Confirmed delete operation
							// TODO: Database action
							$.mobile.referencedLoading('show');
							$.ajax({
								url: '/lib/edit-footprint.php',
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

      $('#footprintList').on('click','[name=editFootprint]', function(evt) {
				var parent = $(evt.currentTarget).closest('li');
        var id = $(evt.currentTarget).attr('value');

        if( id )
        {
            openExternalPopup({
							url: '/pages/popup-editfootprint.php?id='+id+'&method=edit',
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
											url: '/lib/json.footprints.php?id='+id,
											cache: false,
							        dataType: 'json',
											success: function(data) {
												// Rebuild
												parent.attr('data-filtertext', data.name);
												parent.find('h2').text( data.name);
												parent.find('img').attr( 'src', '/img/footprint/' + data.pict_fname);
												$('#footprintList').listview('refresh');
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

			$('#footprintList').on('click','[name=copyFootprint]', function(evt) {
				var parent = $(evt.currentTarget).closest('li');
        var id = $(evt.currentTarget).attr('value');
        if( id )
        {
            openExternalPopup({
							url: '/pages/popup-editfootprint.php?id='+id+'&method=copy',
							customEventName: "positiveResponse",
							customEventHandler: function(evt, data) {
								var action = data.buttonresult;

								switch( action ) {
									case 'cancel':
										break;
									case 'ok':
										// Reload item
										$.mobile.referencedLoading('show');
										debugger;
										$.ajax({
											url: '/lib/json.footprints.php?id='+data.id,
											cache: false,
							        dataType: 'json',
											success: function(data) {
												// Rebuild

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
        }
      });
    });
	</script>

  <div data-role="header" data-position="fixed">
    <h1 uilang="editFootprints"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<button id="newFootprint" class="ui-btn ui-btn-right ui-btn-icon-notext ui-btn-inline ui-icon-fa-plus" uilang="add"></button>
  </div>

  <div role="main" class="ui-content">

		<h3 uilang="footprints"></h3>

    <div class="ui-grid-solo" style=" flex: 2; display: flex; flex-flow: column">
      <div class="ui-block-a"><p name="dialogMessage" uilang="popupFootprintFilterHint"></p></div>
      <div class="ui-block-a" style="flex: 3; display: flex; flex-flow: column">
        <ul id="footprintList" data-role="listview" data-inset="true" data-filter="true" uilang="data-filter-placeholder:popupFootprintFilterPlaceholder" data-autodividers="true" style="flex: 4; overflow-y: auto; padding: 10px">
          <?php echo join("\n", $footprints); ?>
        </ul>
      </div>
    </div>
  </div>

  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
</div>
