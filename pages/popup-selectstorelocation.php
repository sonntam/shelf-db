<?php
  require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

  // Get footprints
  $storelocs = $pdb->StoreLocation()->GetAll();


  foreach( $storelocs as &$s ) {
    $name = htmlspecialchars($s['name']);
    $s = "<li><a href='#' storename='".$name."' storeid=".$s['id']." data-rel='back' data-transition='flow' >".$name."</a></li>";
  }
?>
<div data-role="popup" id="popupStorageLocationDialog" data-overlay-theme="a" data-theme="a"
  data-dismissible="false" style="display: flex; flex-flow: column"> <!-- position: fixed; height: 95%; width: 95%; -->
    <div data-role="header" data-theme="a">
      <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupStorelocationHeader"></h1>
    </div>
    <div role="main" class="ui-content" style="display: flex; flex-flow: column">
      <div style="flex: 0 0 auto">
        <h3 name="dialogHeadline" class="ui-title" uilang="popupStorelocationUserAction"></h3>
        <p name="dialogMessage" uilang="popupStorelocationFilterHint"></p>
      </div>
      <div style="display: flex; flex-flow: column">
        <ul data-role="listview" data-inset="true" data-filter="true"
        uilang="data-filter-placeholder:popupStorelocationFilterPlaceholder"
        data-autodividers="true" style="overflow-y: auto; padding: 10px">
          <?php echo join("\n", $storelocs); ?>
        </ul>
      </div>
        <div style="flex: 0 0 auto" class="ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><a href="#" buttonresult="ok" name="popupAddBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a"><i class="fa fa-plus"></i> <span uilang="add"></span></a></div>
        </div>
    </div>
</div>
<div id="popup-selectstorelocation-dialog"></div>

<script type="text/javascript">
  $('[name=popupAddBtn]').click( function(evt) {

    ShelfDB.GUI.Popup.inputPopUp({
      header: Lang.get('editFootprintAdd'),
      headline: Lang.get('editFootprintNewName'),
      message: Lang.get('editFootprintAddHint'),
      confirmButtonText: Lang.get('add'),
      textLabel: Lang.get('name')+":",
      textPlaceholder: Lang.get('name'),
      ok: function(value){
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
    });
  });
</script>
