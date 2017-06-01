<div id=test data-role="page">

  <!--<script>

    $(':mobile-pagecontainer').off("pagecontainerchange");
    $(':mobile-pagecontainer').on("pagecontainerchange", function( event, ui ){
        console.log("DEBUG: pagecontainer - change");
      });

      $(':mobile-pagecontainer').off("pagecontainerbeforeload");
      $(':mobile-pagecontainer').on("pagecontainerbeforeload", function(event,ui) {

        console.log("DEBUG: pagecontainer - beforeload");
      });

  </script>-->

  <div data-role="header">
      <h2>Are you sure?</h2>
      <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
    </div>
  <div role="main" class="ui-content">
    <h2>Are you sure you wish to exit the application?</h2>
    <p>You have unsaved changes. If you exit without saving them, you will lose them.</p>
    <div class="ui-grid-a">
      <div class="ui-block-a">
        <a href="#" id="exit-button" data-rel="back" class="ui-btn ui-btn-b ui-shadow ui-corner-all">Exit</a>
      </div>
      <div class="ui-block-b">
        <a href="#" id="cancel-button" class="ui-btn ui-shadow ui-corner-all">Cancel</a>
      </div>
    </div>
  </div>

  <div data-role="footer">
    <?php include("page-footer.php"); ?>
  </div>
