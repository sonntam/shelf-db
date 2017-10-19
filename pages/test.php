<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=test data-role="page">

  <script>
    $.mobile.pageContainerChangeTasks.push( function( event, ui ){
      console.log("DEBUG: pagecontainer - change");

      $('#testextpopup').click( function(evt) {
        ShelfDB.GUI.Popup.openExternalPopup({url: '/pages/popup-selectfootprint.php'});
      });

      $('#testextpopup2').click( function(evt) {
        ShelfDB.GUI.Popup.openExternalPopup({url: '/pages/popup-selectstorelocation.php'});
      });

      $('#selectSupplier').click( function(evt) {
        ShelfDB.GUI.Popup.openExternalPopup({url: '/pages/popup-selectsupplier.php'});
      });

      // Open external input dialog
      $('#inputmultipopup').click( function( evt) {
        ShelfDB.GUI.Popup.inputMultilinePopUp({
          header: "Header",
          headline: "Headline",
          message: "Message",
          confirmButtonText: "Ok",
          textLabel: "Label",
          textPlaceholder: "Placeholder",
          textDefault: "Default",
        });
      });

      $('#editfootprint').click( function(evt) {
        ShelfDB.GUI.Popup.openExternalPopup({url: '/pages/popup-editfootprint.php', forceReload: true});
      });

      $('#addpartpicture').click( function(evt) {
        ShelfDB.GUI.Popup.openExternalPopup({url: '/pages/popup-uploadfile.php', forceReload: true});
      });

      $('#editfootprintId').click( function(evt) {
        ShelfDB.GUI.Popup.openExternalPopup({url: '/pages/popup-editfootprint.php?method=edit&id=9', forceReload: true});
      });

      $('#partSearch').click( function() {
        $.ajax({
          type: 'POST',
          dataType: 'json'
        });
      });

      $('#login').click( function() {
        ShelfDB.GUI.Popup.openExternalPopup({
          url: '/pages/popup-login.php'
        });
      });
    });

    $.mobile.pageContainerBeforeLoadTasks.push( function(event,ui) {
      console.log("DEBUG: pagecontainer - beforeload (test.php)");
    });


  </script>

  <div data-role="header" data-position="fixed">
      <h2>Testing page</h2>
      <a href="#navPanel" class="ui-btn"><i class="fa fa-bars"></i></a>
    </div>
  <div role="main" class="ui-content">
    <h2>Here is some clicky stuff for testing</h2>
    <div class="ui-grid-a">
      <div class="ui-block-a">
        <div id="testextpopup" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Footprint popup</div>
        <div id="testextpopup2" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Storage location popup</div>
        <div id="inputmultipopup" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Input Multiline popup</div>
        <div id="editfootprint" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Add footprint popup</div>
        <div id="editfootprintId" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Edit footprint Id popup</div>
        <div id="selectSupplier" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Select supplier dialog</div>
        <div id="partSearch" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Test parts search...</div>
        <div id="addpartpicture" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Part image upload...</div>

        <div id="login" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Login dialog...</div>
        <a href="#" id="exit-button" data-rel="back" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Exit</a>
      </div>
      <div class="ui-block-b">
        <a href="#" id="cancel-button" class="ui-btn ui-shadow ui-corner-all">Cancel</a>
      </div>
    </div>
    <div id="dialog"></div>
  </div>

  <div data-role="footer">
    <?php include("page-footer.php"); ?>
  </div>
