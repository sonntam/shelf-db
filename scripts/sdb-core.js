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

    // Bootstrap 4 Modal zIndex for jqgrid dialogs
    $.jgrid.jqModal.zIndex = 1050;
  };

  // The core submodule
  var coreModule = (function() {

    var pageCreateTasks = [];
    var pageContainerAfterLoadTasks = [];
    var pageContainerBeforeLoadTasks = [];

    var _currentPage = '';

    var _handlePageLoad = function(opts){

      // Parse options
      defaults = {
        reloadHeader: false,
        reloadSidebar: false,
        complete: null,
        url: null,
        data: null,
        forceReload: false
      }
      opts = $.extend({},sdb.Core.PageLoader.defaults,defaults,opts);

      // Sanity checks
      if( !(opts.url) ) return;
      // Do we have to do anything?
      if( !opts.forceReload && ( (
        (location.pathname + location.search === opts.url && location.hash === '')
        || (_stripHash(location.hash) === opts.url && _currentPage === opts.url)
      ) && !(opts.data) && !opts.reloadHeader && !opts.reloadSidebar ) ) return;

      // AJAX request
      var container = opts.pageContainerSelector;

      var res = $(document).triggerHandler('pagebeforeload');
      if( !(typeof res === 'undefined') && res === false ) {
        return;
      }

      $.get( {
        url: opts.url,
        data: opts.data,
        dataType: 'html',
        success: function(responseText, textStatus, jqXHR) {
          var $html = $(responseText);

          // Header
          if( opts.reloadHeader ) {
            var header = $html.closest(opts.headerSelector).first();
            if( header.length > 0 ) {
              $(document).triggerHandler('headerbeforeload');
              $(document).find(opts.headerSelector).replaceWith(header).promise().done( function() {
                $(document).triggerHandler('headerafterload');
              });
            }
          }

          if( opts.reloadSidebar ) {
            var sidebar = $html.closest(opts.sidebarSelector).first();
            if( sidebar.length > 0 ) {
              $(document).triggerHandler('sidebarbeforeload');
              $(document).find(opts.sidebarSelector).replaceWith(header).promise().done( function() {
                $(document).triggerHandler('sidebarafterload');
              });
            }
          }

          // Apply the page
          var page = $html.filter(container);
          if( page.length == 0 ) {
            page = $(responseText).find(container);
          }

          if( page.length > 0 ) {
            // Replace current page
            $(document).find(container).replaceWith(page).promise().done( function() {
              // Trigger events
              if( typeof opts.complete === 'function' )
                opts.complete(responseText, textStatus, jqXHR);
              // Update hash
              location.hash = (opts.url === location.pathname ? '' : opts.url );
              _currentPage = opts.url;
              $(document).triggerHandler('pageafterload');
            });
          } else {
            // Replace the whole document
            var newDoc = document.open("text/html", "replace");
            newDoc.write(responseText);
            newDoc.close();
          }
        }
      });
    };

    var _handlePageLink = function(e){
      var opts = e.data;
      opts.url = $(e.currentTarget).attr('href');

      if( $(e.currentTarget).attr('data-nodefault') ) {
        e.preventDefault();
        e.stopPropagation();
      }
      e.preventDefault();

      _handlePageLoad(opts);
    };

    var _isHashValid = function( hash ) {
      return ( /^#[^#]+$/ ).test( hash );
    };

    var _isPath = function( url ) {
      return ( /\// ).test( url );
    };

    var _stripHash = function( url ) {
      return url.replace( /^#/, '' );
    };

    var pageLoader = {
      // Default options
      defaults: {
        headerSelector: 'header',
        sidebarSelector: 'div.sdb-sidebar',
        pageContainerSelector: 'div[data-rel=pageContainer]'
      },

      // Setup to register pagelink event
      setup: function(opts) {
        defaults = {
          container: 'div[data-rel=pageContainer]',
          data: null
        }
        if( typeof opts === 'undefined' )
          return defaults;

        opts = $.extend({},defaults,opts);
        $.extend(opts, {
          complete: null,
          reloadHeader: false
        });

        $(document).on('click', '[data-rel=pagelink], [target=pagecontainer]', opts, _handlePageLink);

        // Check if we do have an url after the hash, if so - navigate
        var path;
        if( _isHashValid(location.hash) && _isPath(path = _stripHash(location.hash)) ) {
          var loadFn = function() {
            sdb.Core.PageLoader.load({
              url: path
            });
          };
          if( $.isReady ) {
            loadFn();
          } else {
            $(loadFn);
          }

        }
      },

      load: function(opts) {
        // Redirect to handler
        _handlePageLoad(opts);
      }
    };

    return {
      PageLoader: pageLoader,

      pageHookClear: function() {
        console.log("DEBUG: clearing page hooks");
        pageCreateTasks = [];
        pageContainerAfterLoadTasks = [];
        pageContainerBeforeLoadTasks = [];
      },

      pageHookInit: function() {

        $(document).on('pagebeforeload',function(event) {
          console.log("DEBUG: pagebeforeload");
          sdb.Core.pageContainerBeforeLoadTasks.forEach(function(fun) { fun(event); });
          sdb.Core.pageContainerBeforeLoadTasks = [];
        });

        $(document).on('pageafterload', function() {
          console.log("DEBUG: pageafterload");
          sdb.Core.pageContainerAfterLoadTasks.forEach(function(fun) { fun(event); });
          sdb.Core.pageContainerAfterLoadTasks = [];

        });

        $(document).on('popupcreate', function() {
          console.log("DEBUG: popup - create");
        });

        $(document).on('pagecreate', function() {
          console.log("DEBUG: page - create");
          sdb.Core.pageCreateTasks.forEach(function(fun) { fun(event); });
          sdb.Core.pageCreateTasks = [];
        });
      },

      basePath: _basePath,
      pageCreateTasks: pageCreateTasks,
      pageContainerAfterLoadTasks: pageContainerAfterLoadTasks,
      pageContainerBeforeLoadTasks: pageContainerBeforeLoadTasks,

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
          theme: 'a'
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

  $.extend(sdb, {
    Core: coreModule
  });

  // Actions
  _init();

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
