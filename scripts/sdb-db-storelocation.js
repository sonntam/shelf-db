// This is the main ShelfDB javascript framework namespace
var ShelfDB = (function(sdb, $, Lang) {

  // The Part submodule
  var storeLocationModule = (function() {

    var getStoreLocationByIdAsync = function(args) {

      opts = {
        id: null,
        done: null
      };

      $.extend(opts, args);

      sdb.GUI.Core.waitAnimationReferenced('show');
      $.ajax({
        url: sdb.Core.basePath + 'lib/json.storelocations.php',
        type: 'POST',
        dataType: 'json',
        data: {
          id: opts.id,
        }
      }).done(function(data) {
        if( opts.done )
          opts.done(data);

        sdb.GUI.Core.waitAnimationReferenced('hide');
      });
    };

    var createStoreLocationAsync = function(args) {
      opts = {
        name: null,
        done: null
      };

      $.extend(opts, args);

      sdb.GUI.Core.waitAnimationReferenced('show');
      $.ajax({
        url: sdb.Core.basePath + 'lib/edit-storelocation.php',
        type: 'POST',
        dataType: 'json',
        data: {
          name: opts.name,
          method: 'add'
        }
      }).done(function(data) {
        if( opts.done )
          opts.done(data);

        sdb.GUI.Core.waitAnimationReferenced('hide');
      });
    }

    // Return the column model for jqGrid for the parts view
    return  {
      getStoreLocationByIdAsync: getStoreLocationByIdAsync,
      createStoreLocationAsync: createStoreLocationAsync
    };
  })();

  $.extend(sdb, {
    StoreLocation: storeLocationModule,
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery, Lang);
