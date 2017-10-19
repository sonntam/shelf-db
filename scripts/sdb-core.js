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
      basePath: _basePath,
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

        $.mobile.referencedLoading('show', {
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
            $.mobile.referencedLoading('hide');

          },
          error: function(jqXHR, textStatus, errorThrown) {
            // Handle error
            console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
            $.mobile.referencedLoading('hide');

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
            $.mobile.referencedLoading('hide');

            if( typeof opts.error === 'function' )
              opts.error(textStatus, data);
          }

        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle error
          console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
          $.mobile.referencedLoading('hide');

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
