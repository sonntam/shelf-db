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
    coreModule.PageLoader.setup({});
  };

  // The core submodule
  var coreModule = (function() {

    var pageCreateTasks = [];
    var pageContainerBeforeShowTasks = [];
    var pageContainerBeforeLoadTasks = [];
    var pageContainerBeforeChangeTasks = [];
    var pageContainerChangeTasks = [];

    var _handlePageLoad = function(opts){
      // AJAX request
      var container = opts.container;

      var res = $(document).triggerHandler("pagebeforeload");
      if( !(typeof res === 'undefined') && res === false ) {
        return;
      }

      $(container).load(opts.url, opts.data, function(responseText, textStatus, jqXHR) {
        if( typeof opts.complete === 'function' )
          opts.complete(responseText, textStatus, jqXHR);

        $(document).triggerHandler("pageafterload");
      });

    };

    var _handlePageLink = function(e){
      debugger;
      var opts = e.data;
      opts.url = $(e.currentTarget).attr('href');
      opts.complete = null;
      _handlePageLoad(opts);
    };

    var pageLoader = {
      // Setup to register pagelink event
      setup: function(opts) {
        debugger;
        defaults = {
          container: 'div[data-rel=pagecontainer]',
          data: null
        }
        if( typeof opts === 'undefined' )
          return defaults;

        opts = $.extend({},defaults,opts);

        $(document).on('click', '[data-rel=pagelink]', opts, _handlePageLink);
      },

      load: function(opts) {
        defaults = {
          url: '',
          data: null,
          complete: null,
          container: 'main'
        }
        if( typeof opts === 'undefined' )
          return defaults;

        opts = $.extend({},defaults,opts);

        _handlePageLoad(opts);
      }
    };

    return {
      PageLoader: pageLoader,
      pageHookClear: function() {
        console.log("DEBUG: clearing page hooks");
        pageCreateTasks = [];
        pageContainerBeforeShowTasks = [];
        pageContainerBeforeLoadTasks = [];
        pageContainerBeforeChangeTasks = [];
        pageContainerChangeTasks = [];
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

              pageContainerBeforeShowTasks.forEach(function(fun) { fun(event,ui); });
            });

            $(':mobile-pagecontainer').on('pagecontainerbeforeload', function(event,ui) {
              console.log("DEBUG: pagecontainer - beforeload");

              pageContainerBeforeLoadTasks.forEach(function(fun) { fun(event,ui); });

              sdb.Core.pageHookClear();
            });

            $(':mobile-pagecontainer').on('pagecontainerchange', function(event,ui) {
              console.log("DEBUG: pagecontainer - change");

              pageContainerChangeTasks.forEach(function(fun) { fun(event,ui); });

              sdb.Core.pageHookClear();
            });

            $(':mobile-pagecontainer').on('pagecontainerbeforechange', function(event,ui) {
              console.log("DEBUG: pagecontainer - beforechange");

              pageContainerBeforeChangeTasks.forEach(function(fun) { fun(event,ui); });
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

            pageCreateTasks.forEach(function(fun) { fun(); });

            $("[data-role=menupanel]").one("panelbeforeopen", function() {
              // DOH
              //var height = $.mobile.pageContainer.pagecontainer("getActivePage").outerHeight();
              //$(".ui-panel-wrapper").css("height", height+1);
            });
        });
      },
      basePath: _basePath,
      pageCreateTasks: pageCreateTasks,
      pageContainerBeforeShowTasks: pageContainerBeforeShowTasks,
      pageContainerBeforeLoadTasks: pageContainerBeforeLoadTasks,
      pageContainerBeforeChangeTasks: pageContainerBeforeChangeTasks,
      pageContainerChangeTasks: pageContainerChangeTasks,
      uploadFile: function(opts, event) {
        var defaults = {
          uploadTarget: 'tempFile', //'footprintImage', ,'tempImage', 'tempFile' ...
          files: null,
          success: null,
          error: null
        };

        opts = $.extend(true,{},defaults,opts);

        var abortEvent = function(){
          event.stopPropagation();
          event.preventDefault();
        };

        if( event && !opts.files ) {
          opts.files = event.target.files;
        }

        if( opts.files.length <= 0 ) {
          if( event )
            abortEvent();

          return;
        }

        sdb.GUI.Core.waitAnimationReferenced('show', {
          theme: "a"
        });

        // Add the files
        var data = new FormData();
        $.each(opts.files, function(key, value) {
          data.append(key,value);
        });

        $.each({
          type: 'uploadToTemp',
          target: opts.uploadTarget
        }, function(key, value) {
          data.append(key,value);
        });

        $.ajax({
          url: ShelfDB.Core.basePath+'lib/upload-files.php',
          type: 'POST',
          data: data,
          cache: false,
          dataType: 'json',
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if( typeof data.error === 'undefined')
            {
              // Success
              if( typeof opts.success === 'function' )
                opts.success(data);
            } else {
              // Handle error
              if( typeof opts.error === 'function' )
                opts.error(textStatus, data);
            }
            sdb.GUI.Core.waitAnimationReferenced('hide');

          },
          error: function(jqXHR, textStatus, errorThrown) {
            // Handle error
            console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
            sdb.GUI.Core.waitAnimationReferenced('hide');

            if( typeof opts.error === 'function' )
              opts.error(errorThrown, data);
          }
        });
      },
      moveUploadedFile: function(opts) {
        var defaults = {
          targetType: null, //'footprintImage', ...
          tempFilename: null,
          success: null,
          error: null,
          data: null
        };

        opts = $.extend(true,{},defaults,opts);

        $.ajax({
        url: ShelfDB.Core.basePath+'lib/upload-files.php',
        type: 'POST',
        data: {
          tempFilename: opts.tempFilename,
          type: 'moveTempToTarget',
          target: opts.targetType,
        },
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( typeof data.error === 'undefined') {
            // Success -> create new footprint entry in database
            if( typeof opts.success === 'function' )
              opts.success(data, opts.data);
          } else {
            // Handle error
            sdb.GUI.Core.waitAnimationReferenced('hide');

            if( typeof opts.error === 'function' )
              opts.error(textStatus, data);
          }

        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle error
          console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
          sdb.GUI.Core.waitAnimationReferenced('hide');

          if( typeof opts.error === 'function' )
            opts.error(errorThrown, opts.data);
        }
      });
      }
    };
  })();


  // Actions
  _init();

  $.extend(sdb, {
    Core: coreModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
