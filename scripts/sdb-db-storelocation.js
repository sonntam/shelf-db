// This is the main ShelfDB javascript framework namespace
var ShelfDB = (function(sdb, $, Lang) {

  // The Part submodule
  var supplierModule = (function() {

    var getSupplierByIdAsync = function(args) {

      opts = {
        id: null,
        partNr: null,
        done: null
      };

      $.extend(opts, args);

      sdb.GUI.Core.waitAnimationReferenced('show');
      $.ajax({
        url: sdb.Core.basePath + 'lib/json.suppliers.php',
        type: 'POST',
        dataType: 'json',
        data: {
          id: opts.id,
          partNr: opts.partNr
        }
      }).done(function(data) {
        if( opts.done )
          opts.done(data);

        sdb.GUI.Core.waitAnimationReferenced('hide');
      });
    };

    // Return the column model for jqGrid for the parts view
    return  {
      getSupplierByIdAsync: getSupplierByIdAsync
    };
  })();

  $.extend(sdb, {
    Supplier: supplierModule,
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery, Lang);
