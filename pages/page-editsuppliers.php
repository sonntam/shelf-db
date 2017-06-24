<?php
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  // Get suppliers
  $suppliers = $pdb->Suppliers()->GetAll();

	$createListEntryFcn = function( $name, $pictureFilename, $id, $url ) {
		ob_start();
		?>
		<li data-icon="false" data-filtertext="<?php echo $name; ?>">
      <div class="ui-grid-a">
        <div class="ui-block-a" style="max-width: 7em">
          <img style="max-width: 5em; max-height: 5em" src='/img/supplier/<?php echo $pictureFilename; ?>'>
        </div>
        <div class="ui-block-b">
          <h2><?php echo $name; ?></h2>
					<p><a name="supplierLink" target="_blank" href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
        </div>
        <div class="ui-block-c" style="max-width: 22em">
          <div style="margin: 0 0; width: 20.5em" position=relative align=right data-role='controlgroup' data-mini='true' data-type='horizontal'>
            <a href='#' name="deleteSupplier" value="<?php echo $id; ?>" class='ui-btn ui-corner-all ui-icon-delete ui-btn-icon-top' uilang='delete'></a>
            <a href='#' name="editSupplier" value="<?php echo $id; ?>" class='ui-btn ui-corner-all ui-icon-edit ui-btn-icon-top' uilang='edit'></a>
            <a href='#' name="copySupplier" value="<?php echo $id; ?>" class='ui-btn ui-corner-all ui-icon-fa-copy ui-btn-icon-top' uilang='copy'></a>
          </div>
        </div>
      </div>
    </li>
		<?php
		return ob_get_clean();
	};

  foreach( $suppliers as &$f ) {
    $name = htmlspecialchars($f['name']);
		$f = $createListEntryFcn($name, $f['pict_fname'], $f['id'], $pdb->Suppliers()->ExpandRawUrl($f['urlTemplate'], "example"));
  }
?>

<script type="text/javascript">
    window.location="index.php#page-showparts?catid=1";
</script>

<div id=editsuppliers data-role="page">

	<script>
		pageHookClear();

		$.mobile.pageContainerBeforeShowTasks.push( function(event,ui) {
			console.log("DEBUG: pageTask");

			function addNewItem(data) {
				// Add new item
				var elementHtmlDummy = <?php echo json_encode($createListEntryFcn("","","","")); ?>;
				var el = $(elementHtmlDummy).prependTo('#supplierList');
				el.attr('data-filtertext', data.name);
				el.find('h2').text( data.name);
				el.find('img').attr( 'src', '/img/supplier/' + data.pict_fname);
				el.find('div[data-role=controlgroup] a').attr('value',data.id);
				el.find('a[name=supplierLink]').attr('href', data.urlTemplate ).text(data.urlTemplate);
				Lang.searchAndReplace();
				el.enhanceWithin();
				$('#supplierList').listview('refresh');
			}

			$('#newSupplier').click( function(evt) {
				openExternalPopup({
					url: '/pages/popup-editsupplier.php?method=add',
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
									url: '/lib/json.suppliers.php?id='+newid,
									cache: false,
									dataType: 'json',
									success: function(data) {
										// Add new item
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

      $('#supplierList').on('click','[name=deleteSupplier]', function(evt) {
        var id = $(evt.currentTarget).attr('value');
        if( id )
        {
          var entryEl = $(evt.currentTarget).closest('li');
          var entryName = entryEl.first().find('h2').first().text();
          confirmPopUp({
				    header: Lang.get('editSupplierDelete'),
				    text: (Lang.get('editSupplierDeleteHint'))(entryName),
				    confirmButtonText: Lang.get('delete'),
				    confirm: function() {  // Confirmed delete operation
							// TODO: Database action
							$.mobile.referencedLoading('show');
							$.ajax({
								url: '/lib/edit-supplier.php',
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

      $('#supplierList').on('click','[name=editSupplier]', function(evt) {
				var parent = $(evt.currentTarget).closest('li');
        var id = $(evt.currentTarget).attr('value');
        if( id )
        {
            openExternalPopup({
							constrainHeight: false,
							fixedMaxWidth: '525px',
							url: '/pages/popup-editsupplier.php?id='+id+'&method=edit',
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
											url: '/lib/json.suppliers.php?partNr=example&id='+id,
											cache: false,
							        dataType: 'json',
											success: function(data) {
												// Rebuild
												parent.attr('data-filtertext', data.name);
												parent.find('h2').text( data.name);
												parent.find('img').attr( 'src', '/img/supplier/' + data.pict_fname);
												parent.find('div[data-role=controlgroup] a').attr('value',data.id);
												parent.find('a[name=supplierLink]').attr('href', data.urlTemplate ).text(data.urlTemplate);
												$('#supplierList').listview('refresh');
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

			$('#supplierList').on('click','[name=copySupplier]', function(evt) {
				var parent = $(evt.currentTarget).closest('li');
        var id = $(evt.currentTarget).attr('value');
        if( id )
        {
            openExternalPopup({
							url: '/pages/popup-editsupplier.php?id='+id+'&method=copy',
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
											url: '/lib/json.suppliers.php?id='+data.id,
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
    <h1 uilang="editSuppliers"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<button id="newSupplier" class="ui-btn ui-btn-right ui-btn-icon-notext ui-btn-inline ui-icon-fa-plus" uilang="add"></button>
  </div>

  <div role="main" class="ui-content">

		<h3 uilang="suppliers"></h3>

    <div class="ui-grid-solo" style=" flex: 2; display: flex; flex-flow: column">
      <div class="ui-block-a"><p name="dialogMessage" uilang="popupSupplierFilterHint"></p></div>
      <div class="ui-block-a" style="flex: 3; display: flex; flex-flow: column">
        <ul id="supplierList" data-role="listview" data-inset="true" data-filter="true" uilang="data-filter-placeholder:popupSupplierFilterPlaceholder" data-autodividers="true" style="flex: 4; overflow-y: auto; padding: 10px">
          <?php echo join("\n", $suppliers); ?>
        </ul>
      </div>
    </div>
  </div>

  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
</div>
