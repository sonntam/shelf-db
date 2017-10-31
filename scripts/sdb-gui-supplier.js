// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  var supplierModule = (function(){

    var _addNewItem = function(data) {
      // Add new item
      var elementHtmlDummy = data.nodeTemplate;
      var el = $(elementHtmlDummy).prependTo(data.listSelector);
      _updateItem(el, data);
      Lang.searchAndReplace();
    }

    var _updateItem = function(item, data) {
      item.find(data.nameTagSelector).text( data.name );
      item.find('img').attr( 'src', sdb.Core.basePath + 'img/supplier/' + data.pict_fname);
      item.find('a').attr('href', sdb.Core.basePath + 'pages/page-showsearchresults.php?searchMode=supplierId&search='+data.id);
      item.find('button').attr('value', data.id);
      // Trim string

      String.prototype.trimToLength = function(m) {
        return (this.length > m)
          ? $.trim(this).substring(0, m).split(" ").slice(0, -1).join(" ") + "..."
          : this;
      };

      item.find(data.linkTagSelector).attr('href',data.urlTemplate).text(data.urlTemplate.trimToLength(25));
    }

    return {
      Edit: {
        setup: function(opts) {

          var defaults = {
            listSelector: '#searchFilterList',
            listItemSelector: '.list-group-item',
            nameTagSelector: '[name=nameSupplier]',
            nodeTemplate: '',
            addButtonSelector: '#newSupplier',
            copyButtonSelector: '[name=copySupplier]',
            editButtonSelector: '[name=editSupplier]',
            deleteButtonSelector: '[name=deleteSupplier]',
            linkTagSelector: '[name=linkSupplier]',
          };

          opts = $.extend(true, {}, defaults, opts);

          $(opts.addButtonSelector).click( function(evt) {
            ShelfDB.GUI.Popup.openExternalPopup({
    					url: ShelfDB.Core.basePath+'pages/popup-editsupplier.php?method=add',
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
    									url: ShelfDB.Core.basePath+'lib/json.suppliers.php',
                      data: {
                        id: newid,
                        partNr: 'example'
                      },
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
    				    header: Lang.get('editSupplierDelete'),
    				    text: (Lang.get('editSupplierDeleteHint',true))(entryName),
    				    confirmButtonText: Lang.get('delete'),
    				    confirm: function() {  // Confirmed delete operation
    							// TODO: Database action
    							$.ajax({
    								url: ShelfDB.Core.basePath+'lib/edit-supplier.php',
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
    							url: ShelfDB.Core.basePath+'pages/popup-editsupplier.php?id='+id+'&method=edit',
    							customEventName: "positiveResponse",
    							customEventHandler: function(evt, data) {
    								var action = data.buttonresult;

    								switch( action ) {
    									case 'cancel':
    										break;
    									case 'ok':
    										// Reload item

    										$.ajax({
    											url: ShelfDB.Core.basePath+'lib/json.suppliers.php',
                          data: {
                            id:id,
                            partNr:'example'
                          },
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
    							url: ShelfDB.Core.basePath+'pages/popup-editsupplier.php?id='+id+'&method=copy',
    							customEventName: "positiveResponse",
    							customEventHandler: function(evt, data) {
    								var action = data.buttonresult;

    								switch( action ) {
    									case 'cancel':
    										break;
    									case 'ok':
    										// Reload item
    										$.ajax({
    											url: ShelfDB.Core.basePath+'lib/json.suppliers.php?id='+data.id,
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
    Supplier: supplierModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
