// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  var navModule = (function(){

    return {
      Search: {
        setup: function(opts) {

          var defaults = {
            searchHeaderSelector: 'div .search',
            searchBoxSelector: '#searchbar'
          };

          opts = $.extend({}, defaults, opts);

          // Stop closing the menu when trying to select something in the searchbox
          $(opts.searchHeaderSelector).on("swipeleft", function(e){
            e.preventDefault();
            e.stopPropagation();
          });

          $(opts.searchBoxSelector).keypress(function(e){
            if(e.which == 13) {//Enter key pressed
                if( $(e.target).val().trim() != "" ) {
                  $(':mobile-pagecontainer').pagecontainer("change",
                    sdb.Core.basePath + "pages/page-showsearchresults.php?search="+encodeURIComponent($(e.target).val()),
                    {
                      allowSamePageTransition: true,
                      reload: true
                    });
                }
              }
            });
          }
      },
      CategoryTree: {
        getElement: function() {
          return $(_mainTreeSelector);
        },
        setup: function(opts) {

          var defaults = {
            treeSelector: '#categorytree',
            btnCollapseSelector: '#collapse',
            btnExpandSelector: '#expand'
          };

          opts = $.extend({}, defaults, opts);

          _mainTreeSelector = opts.treeSelector;

          // jqTree
          var $tree = $(_mainTreeSelector);

          $tree.tree({
            saveState: true
          });

          // Tree callback
          $tree.bind('tree.click', function(e) {
            // e.node.name - Name string
            // e.node.id   - ID string
            $(':mobile-pagecontainer').pagecontainer("change", sdb.Core.basePath + "pages/page-showparts.php?catid=" + e.node.id + "&showSubcategories=" + Number(e.node.children.length > 0));
          });

          $(opts.btnCollapseSelector).click(function() {
            var tree = $tree.tree('getTree');
            tree.iterate(function(node) {

              if (node.hasChildren()) {
                $tree.tree('closeNode', node, true);
              }
              return true;
            });
          });

          $(opts.btnExpandSelector).click(function() {
            var tree = $tree.tree('getTree');
            tree.iterate(function(node) {

              if (node.hasChildren()) {
                $tree.tree('openNode', node, true);
              }
              return true;
            });
          });
        }
      },
      Logon: {
        setup: function(opts) {
          var defaults = {
            loggedInSelector:  '[data-group=loggedIn]',
            loggedOutSelector: '[data-group=loggedOut]',
            isAdminSelector:   null,
            loginLinkSelector: 'a#navUserLogin',
            logoutLinkSelector:'a#navUserLogout',
            pageAfterLogon:    'index.php'
          };

          opts = $.extend({}, defaults, opts);

          $(opts.loginLinkSelector).click( function() {
            sdb.GUI.Popup.loginPopup({
              loggedInSelector: opts.loggedInSelector,
              loggedOutSelector: opts.loggedOutSelector,
              success: function(data) {
                  // Reload nav and main page
                  $.mobile.pageContainer.change(sdb.Core.basePath+opts.pageAfterLogon);
              }}
            );
          });

          $(opts.logoutLinkSelector).click( function(evt) {
            sdb.GUI.Nav.Logon.logoutUser(opts);
          });

        },
        logoutUser: function(opts) {
          var defaults = {
            loggedInSelector:  null,
            loggedOutSelector: null,
            isAdminSelector:   null,
            pageAfterLogon:    'index.php',
            success:           null
          };

          opts = $.extend({}, defaults, opts);

          $.mobile.referencedLoading('show');

          $.ajax({
            url: sdb.Core.basePath+'lib/edit-user.php',
            data: {
              method: 'logout'
            },
            method: 'post',
            cache: false,
            dataType: 'json',
            success: function(data) {
              if( opts.success && typeof opts.success === 'function')
                opts.success(data);

              // Reload nav and main page
              $.mobile.pageContainer.change(sdb.Core.basePath+opts.pageAfterLogon);

              $(opts.loggedInSelector).addClass('ui-screen-hidden');
              $(opts.loggedOutSelector).removeClass('ui-screen-hidden');

              $.mobile.referencedLoading('hide');
            },
            error: function() {
              // Show error
              $.mobile.referencedLoading('hide');
            }
          });
        }
      }
    };
  })();

  if( typeof sdb.GUI === 'undefined' ) {
    sdb.GUI = {};
  }

  $.extend(sdb.GUI, {
    Nav: navModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
