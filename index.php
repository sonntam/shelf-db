<?php
  include_once(__DIR__.'/classes/partdatabase.class.php');
?>
<!DOCTYPE html>
<html>
  <head lang="de">
    <meta charset="utf-8"/>
    <title uilang="mainTitle"></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-2.1.4.js"></script>
    <!--<script src="./js/jquery-3.2.0.js"></script>-->
    <!--<script src="https://code.jquery.com/jquery-migrate-3.0.0.js"></script>-->

    <!-- JQUERY MOBILE -->
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/lib/jquery.mobile-1.4.5.js"></script>
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/jquery.mobile-1.4.5.menupanel.js"></script>
    <link href="<?php echo $pdb->RelRoot(); ?>/styles/jquery.mobile-1.4.5.css" rel="stylesheet"/>
    <!--<link rel="stylesheet" href="./css/jquery-ui.min.css">-->

    <!-- JQTREE -->
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/lib/tree.jquery.js"></script>
    <link href="<?php echo $pdb->RelRoot(); ?>/styles/jqtree.css" rel="stylesheet"/>
    <!-- <link href="./css/jqtree.style.css" rel="stylesheet"/> -->

    <!-- FONT AWESOME -->
    <link href="<?php echo $pdb->RelRoot(); ?>/styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

    <!-- JQUERY UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/themes/redmond/jquery-ui.min.css">

    <!-- JQGRID FREE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.14.0/jquery.jqgrid.src.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.14.0/js/i18n/grid.locale-de.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.14.0/css/ui.jqgrid.min.css">

    <!-- jquery-validation -->
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/lib/jquery-validation/jquery.validate.min.js"></script>

    <!-- jquery-mobile-font-awesome -->
    <link rel="stylesheet" href="<?php echo $pdb->RelRoot(); ?>/styles/jqm-font-awesome-usvg-upng.css" />

    <!-- jquery-mobile-simpledialogs2 -->
    <link rel="stylesheet" href="<?php echo $pdb->RelRoot(); ?>/styles/jquery.mobile.simpledialog.css" />
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/lib/jquery.mobile.simpledialog2.js"></script>

    <!-- CUSTOM EXTENSIONS -->
    <link href="<?php echo $pdb->RelRoot(); ?>/styles/shelfdb.css" rel="stylesheet"/>
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/lib/js.cookie.js"></script>
    <script src="<?php echo $pdb->RelRoot(); ?>/scripts/custom.ext.js"></script>

    <!-- Localization -->
    <script type="text/javascript" src="<?php echo $pdb->RelRoot(); ?>/scripts/langprovider.js?language=deDE"></script>

    <script>
      (pageHookClear = function() {
        console.log("DEBUG: clearing page hooks");
        $.mobile.pageCreateTasks = [];
        $.mobile.pageContainerBeforeShowTasks = [];
        $.mobile.pageContainerBeforeLoadTasks = [];
        $.mobile.pageContainerBeforeChangeTasks = [];
        $.mobile.pageContainerChangeTasks = [];
      })();

      $(function(){
        // Document ready
        //
        //$.mobile.linkBindingEnabled = true;
        //$.mobile.ajaxEnabled = true;

        // Search
        $('#searchbar').keypress(function(e){
        if(e.which == 13) {//Enter key pressed
            if( $(e.target).val() != "" ) {
              $(':mobile-pagecontainer').pagecontainer("change",
                "<?php echo $pdb->RelRoot(); ?>/pages/page-showsearchresults.php?catid=0&search="+encodeURIComponent($(e.target).val()),
                {
                  allowSamePageTransition: true,
                  reload: true
                });
            }
          }
        });

        // Panel
        $panel = $("body>[data-role='menupanel']");
        $panel.menupanel({'_transitionClose': false}).enhanceWithin();

        // jqTree
        var $tree = $('#categorytree');

        $tree.tree({
          saveState: true
        });

        // Tree callback
        $tree.bind('tree.click', function(e) {
          // e.node.name - Name string
          // e.node.id   - ID string
          $(':mobile-pagecontainer').pagecontainer("change","<?php echo $pdb->RelRoot(); ?>/pages/page-showparts.php?catid=" + e.node.id + "&catrecurse=" + Number(e.node.children.length > 0));
        });

        $('#collapse').click(function() {
          var tree = $tree.tree('getTree');
          tree.iterate(function(node) {

            if (node.hasChildren()) {
              $tree.tree('closeNode', node, true);
            }
            return true;
          });
        });

        $('#expand').click(function() {
          var tree = $tree.tree('getTree');
          tree.iterate(function(node) {

            if (node.hasChildren()) {
              $tree.tree('openNode', node, true);
            }
            return true;
          });
        });
      });

      $(document).on("pagebeforecreate", function(evt) {
        // Apply language
        Lang.searchAndReplace();
      });

      $(document).one('pagecreate', function() {
          console.log("DEBUG: page - create (once)");

          $(':mobile-pagecontainer').on('pagecontainerbeforeshow', function(event,ui) {
            console.log("DEBUG: pagecontainer - beforeshow");

            $.mobile.pageContainerBeforeShowTasks.forEach(function(fun) { fun(event,ui); });
          });

          $(':mobile-pagecontainer').on('pagecontainerbeforeload', function(event,ui) {
            console.log("DEBUG: pagecontainer - beforeload");

            $.mobile.pageContainerBeforeLoadTasks.forEach(function(fun) { fun(event,ui); });
          });

          $(':mobile-pagecontainer').on('pagecontainerchange', function(event,ui) {
            console.log("DEBUG: pagecontainer - change");

            $.mobile.pageContainerChangeTasks.forEach(function(fun) { fun(event,ui); });

            pageHookClear();
          });

          $(':mobile-pagecontainer').on('pagecontainerbeforechange', function(event,ui) {
            console.log("DEBUG: pagecontainer - beforechange");

            $.mobile.pageContainerBeforeChangeTasks.forEach(function(fun) { fun(event,ui); });
          });
      });
      $(document).on('popupcreate', function() {
        console.log("DEBUG: popup - create");
        Lang.searchAndReplace();
      });
      $(document).on('pagecreate', function() {
          console.log("DEBUG: page - create");

          $.mobile.pageCreateTasks.forEach(function(fun) { fun(); });

          $("[data-role=menupanel]").one("panelbeforeopen", function() {
            var height = $.mobile.pageContainer.pagecontainer("getActivePage").outerHeight();
            $(".ui-panel-wrapper").css("height", height+1);
          });
      });
    </script>
  </head>
  <body class="ui-responsive-panel">
    <div id=index data-role="page">

      <div data-role="header" data-position="fixed">
        <h1 uilang="componentDatabase"></h1>
        <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
      </div>
      <div role="main" class="ui-content">
        <p><a href="<?php echo $pdb->RelRoot(); ?>/pages/test.php">Testlink</a></p>
        <p>

        </p>
      </div>

      <div data-role="footer">
        <?php
          include($pdb->AbsRoot().'/pages/page-footer.php');
        ?>
      </div>
    </div>

    <div data-role="menupanel" id="navpanel" data-position="left" class="ui-page-theme-a">

      <div class="search">
        <a class="homelink" href="index.php"><i class="fa fa-home"></i>&nbsp;ShelfDB</a>
        <input id="searchbar" type="text" data-type="search" data-clear-btn="true" uilang="placeholder:searchPlaceholder">
      </div>

      <div data-role="collapsibleset" data-theme="a" data-content-theme="a">
      <!--<span><a href="#" id="btnSlideCategories"><i class="fa fa-plus-square" aria-hidden="true"></i></a> Kategorien</span>-->
        <div id=categories data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u" data-collapsed="false">
          <h6 uilang="categories"></h6>
            <ul data-role="listview">
              <li><a href="<?php echo $pdb->RelRoot(); ?>/pages/page-editcategories.php"><i class="fa fa-pencil"></i> <span uilang="edit"></span></a></li>
            </ul>

            <div class="ui-grid-a">
              <div class="ui-block-a">
                <a id="collapse" class="ui-btn ui-shadow catkeys" href="#"><i class="fa fa-compress"></i> <span uilang="compress"></span></a>
              </div>
              <div class="ui-block-b">
                <a id="expand" class="ui-btn ui-shadow catkeys" href="#"><i class="fa fa-expand"></i> <span uilang="expand"></span></a>
              </div>
            </div>
            <div id="categorytree" data-url="<?php echo $pdb->RelRoot(); ?>/lib/json.categorytree.php"></div>

        </div>
        <div id=storage data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6 uilang="storageLocations"></h6>
          <ul data-role="listview">
            <li><a href="<?php echo $pdb->RelRoot(); ?>/pages/page-editstorelocation.php"><i class="fa fa-edit"></i> <span uilang="edit"></span></a></li>
            <li><a href="#"><i class="fa fa-square-o"></i> <span uilang="storageLocationShowNonEmpty"></span></a></li>
            <li><a href="#"><i class="fa fa-square"></i> <span uilang="storageLocationShowEmpty"></span></a></li>
          </ul>
        </div>
        <div id=tools data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6 uilang="tools"></h6>
          <ul data-role="listview">
            <li><a href="#"><i class="fa fa-th"></i> <span uilang="noFootprintShow"></span></a></li>
            <li><a href="#"><i class="fa fa-microchip"></i> <span uilang="icLogosShow"></span></a></li>
            <li><a href="#"><i class="fa fa-line-chart"></i> <span uilang="statisticsShow"></span></a></li>
            <li><a href="#"><i class="fa fa-shopping-cart"></i> <span uilang="orderItemsShow"></span></a></li>
          </ul>
        </div>
        <div id=suppliers data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6 uilang="suppliers"></h6>
          <ul data-role="listview">
            <li><a href="<?php echo $pdb->RelRoot(); ?>/pages/page-editsuppliers.php"><i class="fa fa-edit"></i> <span uilang="edit"></span></a></li>
          </ul>
        </div>
        <div id=footprints data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6 uilang="footprints"></h6>
          <ul data-role="listview">
            <li><a href="<?php echo $pdb->RelRoot(); ?>/pages/page-editfootprints.php"><i class="fa fa-edit"></i> <span uilang="edit"></span></a></li>
          </ul>
        </div>
      </div>
    </div>
  </body>
</html>
