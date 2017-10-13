// This is the main ShelfDB javascript framework namespace
var ShelfDB = (function(sdb, $) {

  // This databases base path (the path of the file this script is included from, should be index.php)
  var _basePath = (function() {
    var getUrl = window.location;
    var pathParts = getUrl.pathname.split('/');
    pathParts.splice(-1,1);
    return '/' + pathParts.join('/');
  })();

  var _init = function() {
    coreModule.pageHookClear();
  }

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
