// This is the main ShelfDB javascript framework namespace
var ShelfDB = (function(sdb, $, Lang) {

  // The Part submodule
  var partsModule = (function() {

    var getListViewModel = function(opts) {

      defaults = {
        imageFormatter: null
      };

      opts = $.extend(defaults, opts);

      var _linkFormatterFactory = function(parameter, dataFieldName) {
        return function (cellvalue, options, rowObject) {

  				var retstr = '';

  				retstr = '<a href="' + sdb.Core.basePath + 'pages/page-showsearchresults.php?searchMode='+parameter+'&search='+rowObject[dataFieldName]+'">'
  									+ cellvalue + '</a>';

  				return retstr;
  			};
      };

      return [
          {
            name: 'image',
            label: Lang.get('image'),
            index: 'mainpicfile',
            fixed: true,
            width: 32+5 /*2+2+1 padding + border*/,
            sortable: false,
            editable: false,
            align: 'center',
            formatter: opts.imageFormatter,
            search: false,
          },
          {
            name: 'name',
            label: Lang.get('name'),
            index: 'name',
            sortable: true,
            align: 'left',
            editrules: {
              required: true
            },
            formatter: 'showlink',
            formatoptions: {
              idName: 'partid',
              baseLinkUrl: sdb.Core.basePath + 'pages/page-showpartdetail.php',
              target: 'pagecontainer'
            },
            width: 40
          },
          {
            name: 'instock',
            label: Lang.get('inStockColumnHeader'),
            index: 'instock',
            sortable: true,
            align: 'right',
            template: 'integer',
            width: 40,
            fixed: true,
            editable: function(opts) {
              return (opts.mode == 'edit' ? false : true);
            },
            editrules: {
              integer: true,
              minValue: 0,
              edithidden: true
            }
          },
          {
            name: 'totalstock',
            label: Lang.get('totalStockColumnHeader'),
            index: 'totalstock',
            sortable: true,
            align: 'right',
            template: 'integer',
            width: 40,
            fixed: true,
            editable: function(opts) {
              return (opts.mode == 'edit' ? false : true);
            },
            editrules: {
              integer: true,
              minValue: 0,
              edithidden: true
            }
          },
          {
            name: 'mininstock',
            label: Lang.get('minStockColumnHeader'),
            index: 'mininstock',
            sortable: true,
            align: 'right',
            template: 'integer',
            width: 40,
            fixed: true,
            editrules: {
              required: true,
              minValue: 0,
              integer: true
            }
          },
          {
            name: 'footprint',
            label: Lang.get('footprint'),
            index: 'footprint',
            sortable: true,
            align: 'right',
            edittype: 'select',
            formatter: _linkFormatterFactory('footprintId', 'id_footprint'),
            stype: 'select',
            searchoptions: {
              sopt: ['eq','ne'],
              dataUrl: sdb.Core.basePath + 'lib/json.footprints.php?' + $.param({
                method: 'gridFilter'
              })
            },
            editoptions: {
              dataUrl: sdb.Core.basePath + 'lib/json.footprints.php?' + $.param({
                method: 'gridFilter'
              })
            },
            width: 10
          },
          {
            name: 'storeloc',
            label: Lang.get('storageLocation'),
            index: 'storelocid',
            sortable: true,
            align: 'right',
            edittype: 'select',
            //formatter: 'select',
            formatter: _linkFormatterFactory('storageLocationId', 'id_storeloc'),
            stype: 'select',
            searchoptions: {
              sopt: ['eq','ne'],
              dataUrl: sdb.Core.basePath + 'lib/json.storelocations.php?' + $.param({
                method: 'gridFilter'
              })
            },
            editoptions: {
              dataUrl: sdb.Core.basePath + 'lib/json.storelocations.php?' + $.param({
                method: 'gridFilter'
              })
            },
            width: 10,
          },
          /*
          {

            name: 'datasheet',
            label: 'Datenblätter',
            template: 'datasheet',
            sortable: false,
            align: 'left',
            width: 80,
            fixed: true,
            editable: function(opts) {
              return (opts.mode == 'edit' ? false : true);
            },
            search: false,
          },
          */
          {
            name: 'actions',
            label: Lang.get('actions'),
            template: 'actions',
            align: 'center',
            formatter: 'actions',
            formatoptions: {
              keys: true
            }
          },
          {
            label: Lang.get('category'),
            name: 'category_name',
            index: 'category_name',
            hidden: true,
            sortable: true,
            editable: true,
            edittype: 'select',
            formatter: 'select',
            stype: 'select',
            searchoptions: {
              sopt: ['eq','ne'],
              dataUrl: sdb.Core.basePath + 'lib/json.categorytree.php?' + $.param({
                flat: true,
                method: 'gridFilter'
              }),
              searchhidden: true
            },
            editrules: {
              edithidden: true
            },
            editoptions: {
              dataUrl: sdb.Core.basePath + 'lib/json.categorytree.php?' + $.param({
                flat: true,
                method: 'gridFilter'
              })
            }
          }
      ];
    };

    var editPartFieldData = function( id, fieldName, fieldData, success ) {
  		sdb.GUI.Core.waitAnimationReferenced('show');
  		$.ajax({
  			url: sdb.Core.basePath + 'lib/edit-part.php',
  			data: {
  				id: id,
  				field: fieldName,
  				data: fieldData,
  				method: 'editDetail'
  			},
  			type: 'POST',
  			dataType: 'json'
  		}).done(function(data) {
  			if( data && data.success && success) {
  				success(data);
  			}
  			sdb.GUI.Core.waitAnimationReferenced('hide');
  		});
  	};

    var addPartPicture = function( id, imageFileName, success ) {
  		sdb.GUI.Core.waitAnimationReferenced('show');
  		$.ajax({
  			url: sdb.Core.basePath + 'lib/edit-part.php',
  			data: {
  				id: id,
          imageFileName: imageFileName,
  				method: 'addPicture'
  			},
  			type: 'POST',
  			dataType: 'json'
  		}).done(function(data) {
  			if( data && data.success && success) {
  				success(data);
  			}
  			sdb.GUI.Core.waitAnimationReferenced('hide');
  		});
  	};

    var addPartDatasheet = function( id, filename, success ) {
  		sdb.GUI.Core.waitAnimationReferenced('show');
  		$.ajax({
  			url: sdb.Core.basePath + 'lib/edit-part.php',
  			data: {
  				id: id,
          datasheetFileName: filename,
  				method: 'addDatasheet'
  			},
  			type: 'POST',
  			dataType: 'json'
  		}).done(function(data) {
  			if( data && data.success && success) {
  				success(data);
  			}
  			sdb.GUI.Core.waitAnimationReferenced('hide');
  		});
  	};

    var getPartFieldData = function( id, fieldName, success ) {
  		sdb.GUI.Core.waitAnimationReferenced('show');
  		$.ajax({
  			url: sdb.Core.basePath + 'lib/json.parts.php',
  			data: {
  				partid: id,
  				getDetailed: true,
  			},
  			type: 'POST',
  			dataType: 'json'
  		}).done(function(data) {
  			if( data  && success && data.hasOwnProperty(fieldName)) {
			    success(data[fieldName]);
  			}
  			sdb.GUI.Core.waitAnimationReferenced('hide');
  		});
  	};

    var _addPartCount = function(opts) {
      var defaults = {
        id: null,
        type: "",
        inc: 0,
        success: null
      };

      opts = $.extend(defaults,opts);
      opts.type = opts.type.toLowerCase();

      if( opts.id === null ) return false;

      switch( opts.type ) {
        case 'totalstock':
        case 'instock':
        case 'mininstock':
          getPartFieldData(opts.id, opts.type, function(oldData) {
              var newData = parseInt(oldData) + parseInt(opts.inc);
              editPartFieldData(opts.id, opts.type, newData, function() {
                if( opts.success )
                  opts.success(newData);
              } );
          });
          break;
        default:
          return false;
      }

      return true;
    };

    // Return the column model for jqGrid for the parts view
    return  {
      getListViewModel: getListViewModel,
      incrementTotal: function(id, success) { return _addPartCount({id:id, type: "totalstock", inc:1, success:success}); },
      decrementTotal: function(id, success) { return _addPartCount({id:id, type: "totalstock", inc:-1, success:success}); },
      incrementStock: function(id, success) { return _addPartCount({id:id, type: "instock", inc:1, success:success}); },
      decrementStock: function(id, success) { return _addPartCount({id:id, type: "instock", inc:-1, success:success}); },
      editPartFieldData: editPartFieldData,
      addPartPicture: addPartPicture,
      addPartDatasheet: addPartDatasheet
    };
  })();

  $.extend(sdb, {
    Part: partsModule,
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery, Lang);
