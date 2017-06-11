<?php
  require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  // Get footprints
  $storelocs = $pdb->GetStorelocations();


  foreach( $storelocs as &$s ) {
    $name = htmlspecialchars($s['name']);
    $s = "<li><a href='#' storename='".$name."' storeid=".$s['id']." data-rel='back' data-transition='flow' >".$name."</a></li>";
  }
?>
<div data-role="popup" id="popupStorageLocationDialog" data-overlay-theme="a" data-theme="a" data-dismissible="false" style="display: flex; flex-flow: column"> <!-- position: fixed; height: 95%; width: 95%; -->
    <div data-role="header" data-theme="a">
      <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupStorelocationHeader"></h1>
    </div>
    <div role="main" class="ui-content" style="flex: 1; display: flex; flex-flow: column">
        <h3 name="dialogHeadline" class="ui-title" uilang="popupStorelocationUserAction"></h3>
        <div class="ui-grid-solo" style=" flex: 2; display: flex; flex-flow: column">
        <div class="ui-block-a"><p name="dialogMessage" uilang="popupStorelocationFilterHint"></p></div>
        <div class="ui-block-a" style="flex: 3; display: flex; flex-flow: column">
          <ul data-role="listview" data-inset="true" data-filter="true" uilang="data-filter-placeholder:popupStorelocationFilterPlaceholder" data-autodividers="true" style="flex: 4; overflow-y: auto; padding: 10px">
            <?php echo join("\n", $storelocs); ?>
          </ul>
        </div>
        <div class="ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><a href="#" buttonresult="ok" name="popupAddBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a"><i class="fa fa-plus"></i> <span uilang="add"></span></a></div>
        </div>
    </div>
</div>
<div id="popup-storelocselect-dialog"></div>

<script type="text/javascript">
  $('[name=popupAddBtn]').click( function(evt) {

    inputPopUp(
      Lang.get('editFootprintAdd'),
      Lang.get('editFootprintNewName'),
      Lang.get('editFootprintAddHint'),
      Lang.get('add'),
      Lang.get('name')+":", Lang.get('name'), "", function(value){
        // Set name AJAX call to mysql script
        $.ajax({
          url: '/lib/edit-footprint.php',
          type: 'POST',
          dataType: 'json',
          data: {
            method: 'add',
            newname: value,
          }
        }).done(function(data) {

          if( data['success'] == true )
          {
              // Open tree to new node (but do not select it)
              var data = {
                name: data['newname'],
                id: data['newid'],

              };
          }
        });
      }
    );
  });
</script>
