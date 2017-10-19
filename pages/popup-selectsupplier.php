<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  // Get suppliers
  $suppliers = $pdb->Supplier()->GetAll();

  $createListEntryFcn = function( $name, $pictureFilename, $id, $url ) {
		ob_start();
		?>
		<li data-filtertext="<?php echo $name; ?>">
      <!--<div class="ui-grid-a">
        <div class="ui-block-a" style="max-width: 7em">
          <img class="ui-center-element-absolute" style="max-width: 5em; max-height: 5em" src='/img/supplier/<?php echo $pictureFilename; ?>'>
        </div>
        <div class="ui-block-b">
          <h2><?php echo $name; ?></h2>
					<p><a name="supplierLink" target="_blank" href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
        </div>
      </div> -->
      <a data-rel='back' href='#' supplierid=<?php echo $id; ?> suppliername='<?php echo $name; ?>'>
        <img class="ui-center-element-absolute" class="ui-center-element" src='/img/supplier/<?php echo $pictureFilename; ?>'>
        <h2><?php echo $name; ?></h2>
      </a>
    </li>
		<?php
		return ob_get_clean();
	};

  foreach( $suppliers as &$f ) {
    $name = htmlspecialchars($f['name']);
		$f = $createListEntryFcn($name, $f['pict_fname'], $f['id'], $pdb->Supplier()->ExpandRawUrl($f['urlTemplate'], "example"));
  }
?>

<div data-role="popup" id="popupSupplierSelectDialog" data-overlay-theme="a" data-theme="a"
  data-dismissible="false" style="display: flex; flex-flow: column"> <!-- position: fixed; height: 95%; width: 95%; -->
    <div data-role="header" data-theme="a">
      <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupSupplierSelectHeader"></h1>
    </div>
    <div role="main" class="ui-content" style="display: flex; flex-flow: column">
      <div style="flex: 0 0 auto">
        <h3 name="dialogHeadline" class="ui-title" uilang="popupSupplierSelectUserAction"></h3>
        <p name="dialogMessage" uilang="popupSupplierFilterHint"></p>
      </div>
      <div style="display: flex; flex-flow: column">
        <ul data-role="listview" data-inset="true" data-filter="true"
          uilang="data-filter-placeholder:popupSupplierFilterPlaceholder"
          data-autodividers="true" style="padding: 10px; overflow-y: auto">
          <?php echo join("\n", $suppliers); ?>
        </ul>
      </div>
      <div style="flex: 0 0 auto" class="ui-grid-a">
        <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
        <div class="ui-block-b"><a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" data-rel="back" data-transition="flow" uilang="ok"></a></div>
      </div>
    </div>
</div>
