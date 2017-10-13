<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=test data-role="page">

  <script>
    pageHookClear();

    $.mobile.pageContainerChangeTasks.push( function( event, ui ){
      console.log("DEBUG: pagecontainer - change");

      $('#testextpopup').click( function(evt) {
        openExternalPopup({url: '/pages/popup-selectfootprint.php'});
      });

      $('#testextpopup2').click( function(evt) {
        openExternalPopup({url: '/pages/popup-selectstorelocation.php'});
      });

      $('#selectSupplier').click( function(evt) {
        openExternalPopup({url: '/pages/popup-selectsupplier.php'});
      });

      // Open external input dialog
      $('#inputmultipopup').click( function( evt) {
        inputMultilinePopUp({
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
        openExternalPopup({url: '/pages/popup-editfootprint.php', forceReload: true});
      });

      $('#editfootprintId').click( function(evt) {
        openExternalPopup({url: '/pages/popup-editfootprint.php?method=edit&id=9', forceReload: true});
      });

      $('#partSearch').click( function() {
        $.ajax({
          type: 'POST',
          dataType: 'json'
        });
      });
    });

    $.mobile.pageContainerBeforeLoadTasks.push( function(event,ui) {
      console.log("DEBUG: pagecontainer - beforeload (test.php)");
    });


  </script>

  <div data-role="header">
      <h2>Are you sure?</h2>
      <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
    </div>
  <div role="main" class="ui-content">
    <h2>Are you sure you wish to exit the application?</h2>
    <p>You have unsaved changes. If you exit without saving them, you will lose them.</p>
    <div class="ui-grid-a">
      <div class="ui-block-a">
        <div id="testextpopup" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Footprint popup</div>
        <div id="testextpopup2" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Storage location popup</div>
        <div id="inputmultipopup" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Input Multiline popup</div>
        <div id="editfootprint" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Add footprint popup</div>
        <div id="editfootprintId" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Edit footprint Id popup</div>
        <div id="selectSupplier" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Select supplier dialog</div>
        <div id="partSearch" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Test parts search...</div>
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
