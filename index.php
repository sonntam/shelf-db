<!DOCTYPE html>
<html>
  <head lang="de">
    <meta charset="utf-8"/>
    <title>PART-DB Elektronische Bauteile-Datenbank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-2.1.4.js"></script>
    <!--<script src="./js/jquery-3.2.0.js"></script>-->
    <!--<script src="https://code.jquery.com/jquery-migrate-3.0.0.js"></script>-->

    <!-- JQUERY MOBILE -->
    <script src="./scripts/lib/jquery.mobile-1.4.5.js"></script>
    <script src="./scripts/jquery.mobile-1.4.5.menupanel.js"></script>
    <link href="./styles/jquery.mobile-1.4.5.css" rel="stylesheet"/>
    <!--<link rel="stylesheet" href="./css/jquery-ui.min.css">-->

    <!-- JQTREE -->
    <script src="./scripts/lib/tree.jquery.js"></script>
    <link href="./styles/jqtree.css" rel="stylesheet"/>
    <!-- <link href="./css/jqtree.style.css" rel="stylesheet"/> -->

    <!-- FONT AWESOME -->
    <link href="./styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

    <!-- JQUERY UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/themes/redmond/jquery-ui.min.css">

    <!-- JQGRID FREE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.14.0/jquery.jqgrid.src.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.14.0/js/i18n/grid.locale-de.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.14.0/css/ui.jqgrid.min.css">

    <!-- CUSTOM EXTENSIONS -->
    <link href="./styles/partdb_ms.css" rel="stylesheet"/>
    <script src="./scripts/lib/js.cookie.js"></script>
    <script src="./scripts/custom.ext.js"></script>


    <script>

      var $firstInit = true;

      $(function(){
        // Document ready
        //
        //$.mobile.linkBindingEnabled = true;
        //$.mobile.ajaxEnabled = true;

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
          $(':mobile-pagecontainer').pagecontainer("change","/pages/page-showparts.php?catid=" + e.node.id + "&catrecurse=" + Number(e.node.children.length > 0));
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

      $(document).on('pagecreate', function() {
          console.log("DEBUG: page - create");
          if( $firstInit == true )
          {
            $(':mobile-pagecontainer').on('pagecontainerbeforeshow', function(event,ui) {
              console.log("DEBUG: pagecontainer - beforeshow");
            });
            $firstInit = false;
          }

          $("[data-role=menupanel]").one("panelbeforeopen", function() {
            var height = $.mobile.pageContainer.pagecontainer("getActivePage").outerHeight();
            $(".ui-panel-wrapper").css("height", height+1);
          });
      });
    </script>
  </head>
  <body class="ui-responsive-panel">
    <div id=index data-role="page">

      <div data-role="header">
        <h1>Bauteiledatenbank</h1>
        <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
      </div>
      <div role="main" class="ui-content">
        <p><a href="pages/test.php">Testlink</a></p>
        <p>

        </p>
      </div>

      <div data-role="footer">
        <?php
          include(__DIR__.'/pages/page-footer.php');
        ?>
      </div>
    </div>

    <div data-role="menupanel" id="navpanel" data-position="left" class="ui-page-theme-a">

      <div class="search">
        <a class="homelink" href="index.php"><i class="fa fa-home"></i>&nbsp;PartDB</a>
        <input id="searchbar" type="text" data-type="search" data-clear-btn="true" placeholder="Suche...">
      </div>

      <div data-role="collapsibleset">
      <!--<span><a href="#" id="btnSlideCategories"><i class="fa fa-plus-square" aria-hidden="true"></i></a> Kategorien</span>-->
        <div id=categories data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u" data-collapsed="false">
          <h6>Kategorien</h6>
            <ul data-role="listview">
              <li><a href="/pages/page-editcategories.php"><i class="fa fa-pencil"></i> Bearbeiten</a></li>
            </ul>

            <div class="ui-grid-a">
              <div class="ui-block-a">
                <a id="collapse" class="ui-btn ui-shadow catkeys" href="#"><i class="fa fa-compress"></i> Zuklappen</a>
              </div>
              <div class="ui-block-b">
                <a id="expand" class="ui-btn ui-shadow catkeys" href="#"><i class="fa fa-expand"></i> Aufklappen</a>
              </div>
            </div>
            <div id="categorytree" data-url="/lib/json.categorytree.php"></div>

        </div>
        <div id=storage data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6>Lagerorte</h6>
            <ul data-role="listview">
              <li><a href="#"><i class="fa fa-edit"></i> Bearbeiten</a></li>
            </ul>

        </div>
        <div id=tools data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6>Tools</h6>

        </div>
        <div id=suppliers data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6>Lieferanten</h6>

        </div>
        <div id=footprints data-role="collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u">
          <h6>Footprints</h6>

        </div>
      </div>
    </div>
  </body>
</html>
