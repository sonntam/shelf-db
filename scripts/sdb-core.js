// This is the main ShelfDB javascript framework namespace
var ShelfDB = (function(sdb, $) {

  // This databases base path (the path of the file this script is included from, should be index.php)
  var _basePath = (function() {
    var getUrl = window.location;
    var pathParts = getUrl.pathname.split('/');
    pathParts.splice(-1,1);
    return pathParts.join('/')+'/';
  })();

  var _init = function() {
    coreModule.pageHookClear();
    coreModule.pageHookInit();
  };

  // The core submodule
  var coreModule = (function() {

    return {
      pageHookClear: function() {
        console.log("DEBUG: clearing page hooks");
        $.mobile.pageCreateTasks = [];
        $.mobile.pageContainerBeforeShowTasks = [];
        $.mobile.pageContainerBeforeLoadTasks = [];
        $.mobile.pageContainerBeforeChangeTasks = [];
        $.mobile.pageContainerChangeTasks = [];
      },
      pageHookInit: function() {

        $(document).on('updatelayout',function() {
          console.log("DEBUG: updatelayout");
        });

        $(document).on('menupanelopen',function() {
          console.log("DEBUG: menupanelopen");
          $(window).trigger('resize');
        });

        $(document).on('menupanelclose',function() {
          console.log("DEBUG: menupanelclose");
          $(window).trigger('resize');
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

              sdb.Core.pageHookClear();
            });

            $(':mobile-pagecontainer').on('pagecontainerchange', function(event,ui) {
              console.log("DEBUG: pagecontainer - change");

              $.mobile.pageContainerChangeTasks.forEach(function(fun) { fun(event,ui); });

              sdb.Core.pageHookClear();
            });

            $(':mobile-pagecontainer').on('pagecontainerbeforechange', function(event,ui) {
              console.log("DEBUG: pagecontainer - beforechange");

              $.mobile.pageContainerBeforeChangeTasks.forEach(function(fun) { fun(event,ui); });
            });
        });
        $(document).on('popupcreate', function() {
          console.log("DEBUG: popup - create");
        });
        $(document).on('pagecreate', function() {
            console.log("DEBUG: page - create");

            $(document).on( "panelclose", "[data-role=menupanel]", function() {
              console.log('Panel close');
            });

            $.mobile.pageCreateTasks.forEach(function(fun) { fun(); });

            $("[data-role=menupanel]").one("panelbeforeopen", function() {
              var height = $.mobile.pageContainer.pagecontainer("getActivePage").outerHeight();
              $(".ui-panel-wrapper").css("height", height+1);
            });
        });
      },
      basePath: _basePath
    };
  })();


  // Actions
  _init();

  $.extend(sdb, {
    Core: coreModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
