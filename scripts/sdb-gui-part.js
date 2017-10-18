// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  var partModule = (function(){

    var _imageFormatter = function(cellvalue, options, rowObject) {

      var retstr = '';

      retstr = '<img style="max-width: 32px; max-height: 32px; height:auto; '
      + 'width:auto" data-other-src="'+rowObject.mainPicFile+'" src="'+rowObject.mainPicThumbFile+'">'

      retstr = '<a href="#imgViewer" data-rel="popup" data-position-to="window">'
                + retstr + '</a>';

      return retstr;
    };

    return {

      PartList: {
        setup: function(opts) {

          var defaults = {
            listSelector: '#partList',
            baseDataUrl: sdb.Core.basePath + 'lib/json.parts.php',
            caption: '',
            filterParameters: {},
            enableGrouping: true,
            pagerSelector: '#partListPager',
            showGroupingSwitch: true,
            footprintFilterString: 'Any',
            storeLocationFilterString: 'Any',
            categoryFilterString: 'Any',
            groupingSwitch: {
              caption: 'Grouping',
              id: 'chkHideGroups',
              onCheck: function(list) {
                // Hide grouping on check
                newParam = $.extend({}, opts.filterParameters, {
                  getSubcategories: 0
                });
                list.jqGrid('setGridParam', {
                  url: opts.baseDataUrl + '?' + $.param(newParam)
                });
              },
              onUnCheck: function(list) {
                newParam = $.extend({}, opts.filterParameters, {
                  getSubcategories: 1
                });
                list.jqGrid('setGridParam', {
                  url: opts.baseDataUrl + '?' + $.param(newParam)
                });
              }
            },
          };

          opts = $.extend(true, {}, defaults, opts);

          $(opts.listSelector).jqGrid({
  					caption: opts.caption,
  					url: opts.baseDataUrl + ( opts.filterParameters ? '?' + $.param(opts.filterParameters) : "" ),
  					editurl: sdb.Core.basePath + 'lib/edit-part.php',
  					autowidth: true,
  					shrinkToFit: true,
  					datatype: 'json',
  					autoencode: true,
  					grouping: opts.enableGrouping,
  					groupingView: {
  						groupField: ['category_name'],
  						groupDataSorted: true,
  						groupColumnShow: [false]
  					},
  					sortable: true,
  					cmTemplate: {
  						autoResizable: true,
  						editable: true
  					},
  					autoResizing: {
  						compact: true
  					},
  					iconSet: 'fontAwesome',
  					rowNum:20,
  					rowList: [20,50,100],
  					pager: opts.pagerSelector,
  					toppager:true,
  					filterToolbar:true,
  					searching: {
  						defaultSearch: 'cn'
  					},
  					inlineEditing: {keys:true, position:"afterSelected"},
  					sortname: 'name',
  					viewrecords: true,
  					sortorder: 'asc',
  					viewrecords: false,
  					gridComplete: function() {

  						var ids = $(this).jqGrid('getDataIDs');
  						for( var i = 0; i < ids.length; i++) {
  								$(this).jqGrid('setRowData', ids[i], {
  									action: '<p>add decrease</p>'
  								});
  						}

  					},
  	        colModel: sdb.Part.getListViewModel({
  						footprintFilterString: opts.footprintFilterString,
  						storeLocationFilterString: opts.storeLocationFilterString,
  						categoryFilterString: opts.categoryFilterString,
  						imageFormatter: _imageFormatter
  					}),
  					/*onSelectRow: function(rowid){
  						debugger;
  						var $self = $(this);
  						var savedRow = $self.jqGrid('getGridParam', 'savedRow');
  						if (savedRow.length > 0) {
  							$self.jqGrid('restoreRow', savedRow[0].id);
  						}
  						//$self.jqGrid('editRow', rowid);
  			  	},*/
          });

  				$(opts.listSelector).jqGrid('navGrid',opts.pagerSelector, {
            edit:false,
            add:true,
            del:false
          },{},{},{},{
  					multipleSearch: true,
  					multipleGroup: false
  				});

  				// Copy toolbar buttons to top toolbar and hide right side of toppager
  				$(opts.pagerSelector+'_left').children().clone(true).appendTo(opts.listSelector+'_toppager_left');
  				$(opts.listSelector+'_toppager_right').hide();
  				if( opts.showGroupingSwitch ) { // Only show category selector if category has children ?>
  					$(opts.listSelector).jqGrid('navCheckboxAdd',opts.listSelector + '_toppager_left', { // '#list_toppager_left'
  	          caption: opts.groupingSwitch.caption,
  						position: 'first',
  						id: opts.groupingSwitch.id,
  	            onChange: function() {
  								if($(this).is(':checked')) {
                    if( opts.groupingSwitch.onCheck && typeof opts.groupingSwitch.onCheck === 'function' ) {
                      opts.groupingSwitch.onCheck($(opts.listSelector));
                    }
                    $(opts.listSelector).trigger('reloadGrid');
  						 		} else {
                    if( opts.groupingSwitch.onUnCheck && typeof opts.groupingSwitch.onUnCheck === 'function' ) {
                      opts.groupingSwitch.onUnCheck($(opts.listSelector));
                    }
                    $(opts.listSelector).trigger('reloadGrid',[{page:1}]);
  						 		}
  	            }
  	 				 });
  				}
    		}
      }
    };
  })();

  if( typeof sdb.GUI === 'undefined' ) {
    sdb.GUI = {};
  }

  $.extend(sdb.GUI, {
    Part: partModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
