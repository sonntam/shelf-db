// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  var footprintModule = (function(){

    var _addNewItem = function(data) {
      // Add new item
      var elementHtmlDummy = data.nodeTemplate;
      var el = $(elementHtmlDummy).prependTo(data.listSelector);
      _updateItem(el, data);
      Lang.searchAndReplace();
    }

    var _updateItem = function(item, data) {
      item.find(data.nameTagSelector).text( data.name );
      item.find('img').attr( 'src', sdb.Core.basePath + 'img/footprint/' + data.pict_fname);
      item.find('a').attr('href', sdb.Core.basePath + 'pages/page-showsearchresults.php?type=footprint&id='+data.id);
      item.find('button').attr('value', data.id);
    }

    return {
      Edit: {
        setup: function(opts) {

          var defaults = {
            listSelector: '#searchFilterList',
            listItemSelector: '.list-group-item',
            nameTagSelector: '[name=nameFootprint]',
            nodeTemplate: '',
            addButtonSelector: '#newFootprint',
            copyButtonSelector: '[name=copyFootprint]',
            editButtonSelector: '[name=editFootprint]',
            deleteButtonSelector: '[name=deleteFootprint]',
          };

          opts = $.extend(true, {}, defaults, opts);

          $(opts.addButtonSelector).click( function(evt) {
            ShelfDB.GUI.Popup.openExternalPopup({
    					url: ShelfDB.Core.basePath+'pages/popup-editfootprint.php?method=add',
    					customEventName: "positiveResponse",
    					customEventHandler: function(evt, data) {
    						var action = data.buttonresult;
    						var newid  = data.id;

    						switch( action ) {
    							case 'cancel':
    								break;
    							case 'ok':
    								// Reload item
    								$.ajax({
    									url: ShelfDB.Core.basePath+'lib/json.footprints.php?id='+newid,
    									cache: false,
    									dataType: 'json',
    									success: function(data) {
                        data = $.extend({},opts,data);
    										_addNewItem(data);
    									},
    									error: function() {

    									}
    								});
    								break;
    						}
    					},
    					forceReload: true
    				});
          });

          $(opts.listSelector).on('click',opts.deleteButtonSelector, function(evt) {
            var id = $(evt.currentTarget).attr('value');

            if( id ) {
              var entryEl = $(evt.currentTarget).closest(opts.listItemSelector);
              var entryName = entryEl.first().find(opts.nameTagSelector).first().text();
              ShelfDB.GUI.Popup.confirmPopUp({
    				    header: Lang.get('editFootprintDelete'),
    				    text: (Lang.get('editFootprintDeleteHint',true))(entryName),
    				    confirmButtonText: Lang.get('delete'),
    				    confirm: function() {  // Confirmed delete operation
    							// TODO: Database action
    							$.ajax({
    								url: ShelfDB.Core.basePath+'lib/edit-footprint.php',
    								type: 'POST',
    								data: {
    									method: 'delete',
    									id: id
    								},
    								dataType: 'json',
    								cache: false,
    								success: function(data) {
    									if( data.success ) {
    										entryEl.remove();
    									}
    								},
    								error: function() {
    								}
    							});
                }
              })
            }
          });

          $(opts.listSelector).on('click',opts.editButtonSelector, function(evt) {
    				var parent = $(evt.currentTarget).closest(opts.listItemSelector);
            var id = $(evt.currentTarget).attr('value');

            if( id ) {
                ShelfDB.GUI.Popup.openExternalPopup({
    							url: ShelfDB.Core.basePath+'pages/popup-editfootprint.php?id='+id+'&method=edit',
    							customEventName: "positiveResponse",
    							customEventHandler: function(evt, data) {
    								var action = data.buttonresult;

    								switch( action ) {
    									case 'cancel':
    										break;
    									case 'ok':
    										// Reload item

    										$.ajax({
    											url: ShelfDB.Core.basePath+'lib/json.footprints.php?id='+id,
    											cache: false,
    							        dataType: 'json',
    											success: function(data) {
    												// Rebuild
    												data = $.extend({},opts,data);
                            _updateItem(parent, data);
    											},
    											error: function() {
    											}
    										});
    										break;
    								}
    							},
    							forceReload: true
    						});
            }
          });

          $(opts.listSelector).on('click',opts.copyButtonSelector, function(evt) {
    				var parent = $(evt.currentTarget).closest('li');
            var id = $(evt.currentTarget).attr('value');

            if( id ) {
                ShelfDB.GUI.Popup.openExternalPopup({
    							url: ShelfDB.Core.basePath+'pages/popup-editfootprint.php?id='+id+'&method=copy',
    							customEventName: "positiveResponse",
    							customEventHandler: function(evt, data) {
    								var action = data.buttonresult;

    								switch( action ) {
    									case 'cancel':
    										break;
    									case 'ok':
    										// Reload item
    										$.ajax({
    											url: ShelfDB.Core.basePath+'lib/json.footprints.php?id='+data.id,
    											cache: false,
    							        dataType: 'json',
    											success: function(data) {
    												// Rebuild
    												data = $.extend({},opts,data);
    												_addNewItem(data);
    											},
    											error: function() {
    											}
    										});
    										break;
    								}
    							},
    							forceReload: true
    						});
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
    Footprint: footprintModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
