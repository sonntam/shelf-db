<?php
  require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  // Get footprints
  $footprints = $pdb->GetFootPrints();


  foreach( $footprints as &$f ) {
    $name = htmlspecialchars($f['name']);
    $f = "<li><a data-rel='back' href='#' footprintid=".$f['id']." footprintname='".$name."'><img src='/img/footprint/".$f['pict_fname']."'><h2>".$name."</h2></a></li>";
  }
?>

<div data-role="popup" id="popupFootprintSelectDialog" data-overlay-theme="a" data-theme="a" data-dismissible="false" style="display: flex; flex-flow: column"> <!-- position: fixed; height: 95%; width: 95%; -->
    <div data-role="header" data-theme="a">
      <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupFootprintHeader"></h1>
    </div>
    <div role="main" class="ui-content" style="flex: 1; display: flex; flex-flow: column">
        <h3 name="dialogHeadline" class="ui-title" uilang="popupFootprintUserAction"></h3>
        <div class="ui-grid-solo" style=" flex: 2; display: flex; flex-flow: column">
        <div class="ui-block-a"><p name="dialogMessage" uilang="popupFootprintFilterHint"></p></div>
        <div class="ui-block-a" style="flex: 3; display: flex; flex-flow: column">
          <ul data-role="listview" data-inset="true" data-filter="true" uilang="data-filter-placeholder:popupFootprintFilterPlaceholder" data-autodividers="true" style="flex: 4; overflow-y: auto; padding: 10px">
            <?php echo join("\n", $footprints); ?>
          </ul>
        </div>
        <div class="ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" data-rel="back" data-transition="flow" uilang="ok"></a></div>
        </div>
    </div>
</div>
