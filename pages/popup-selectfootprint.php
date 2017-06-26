<?php
  require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  // Get footprints
  $footprints = $pdb->Footprints()->GetAll();

  foreach( $footprints as &$f ) {
    $name = htmlspecialchars($f['name']);
    ob_start();
    ?>
      <li>
        <a data-rel='back' href='#' footprintid=<?php echo $f['id']; ?> footprintname='<?php echo $name; ?>'>
          <img class="ui-center-element-absolute" src='/img/footprint/<?php echo $f['pict_fname']; ?>'>
          <h2><?php echo $name; ?></h2>
        </a>
      </li>
    <?php
    $f = ob_get_clean();
  }
?>

<div data-role="popup" id="popupFootprintSelectDialog" data-overlay-theme="a" data-theme="a"
  data-dismissible="false" style="display: flex; flex-flow: column"> <!-- position: fixed; height: 95%; width: 95%; -->
    <div data-role="header" data-theme="a">
      <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupFootprintSelectHeader"></h1>
    </div>
    <div role="main" class="ui-content" style="display: flex; flex-flow: column">
      <div style="flex: 0 0 auto">
        <h3 name="dialogHeadline" class="ui-title" uilang="popupFootprintSelectUserAction"></h3>
        <p name="dialogMessage" uilang="popupFootprintFilterHint"></p>
      </div>
      <div style="display: flex; flex-flow: column">
        <ul data-role="listview" data-inset="true" data-filter="true"
          uilang="data-filter-placeholder:popupFootprintFilterPlaceholder"
          data-autodividers="true" style="padding: 10px; overflow-y: auto">
          <?php echo join("\n", $footprints); ?>
        </ul>
      </div>
      <div style="flex: 0 0 auto" class="ui-grid-a">
        <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
        <div class="ui-block-b"><a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" data-rel="back" data-transition="flow" uilang="ok"></a></div>
      </div>
    </div>
</div>
