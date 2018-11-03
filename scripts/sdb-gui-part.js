// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  var partModule = (function(){

    var _imageFormatter = function(cellvalue, options, rowObject) {

      var retstr = '';

      retstr = '<img style="max-width: 32px; max-height: 32px; height:auto; '
      + 'width:auto" data-other-src="'+rowObject.mainPicFile+'" src="'+rowObject.mainPicThumbFile+'">';

      retstr = '<a href="#" data-toggle="universalPopup">'
                + retstr + '</a>';

      return retstr;
    };

    var _addPictureContainer = function(opts, picId, picFile, picThumbFile) {
      debugger;
      var pnTemplate = $(opts.pictureNodeTemplate);

      pnTemplate.attr('value',picId);
      //pnTemplate.find('img').attr('id','picture-'+picId);
      pnTemplate.find('img').attr('src',picFile);
      pnTemplate.find('img').attr('id', 'picture-'+picId);
      pnTemplate.find('img').attr('data-other-src',picThumbFile);
      pnTemplate.find(opts.pictureElDeleteBtnSelector).attr('value',picId);
      pnTemplate.find(opts.pictureElMasterBtnSelector).attr('value',picId);

      pnTemplate.insertBefore( $(opts.pictureAddElementSelector) )

      Lang.searchAndReplace();
    };

    var _addDatasheetContainer = function(opts, id, file) {
      debugger;
      var pnTemplate = $(opts.datasheetNodeTemplate);

      pnTemplate.attr('value',id);
      //pnTemplate.find('img').attr('id','picture-'+picId);
      pnTemplate.find('a').attr('href',file);
      pnTemplate.find('a').attr('id', 'datasheet-'+id);
      pnTemplate.find(opts.pictureElDeleteBtnSelector).attr('value',id);

      pnTemplate.insertBefore( $(opts.datasheetAddElementSelector) )

      Lang.searchAndReplace();
    };

    var _updateButtons = function(opts) {
      var total = $(opts.totalTextSelector);
      var stock = $(opts.stockTextSelector);

      var nTotal = parseInt(total.val());
      var nStock = parseInt(stock.val());

      if( nTotal <= nStock  || nTotal <= 0 )	// Disable minus total button
        $(opts.totalSubBtnSelector).prop('disabled',true);
      else
        $(opts.totalSubBtnSelector).prop('disabled',false);

      if( nStock >= nTotal )
        $(opts.stockAddBtnSelector).prop('disabled',true);
      else
        $(opts.stockAddBtnSelector).prop('disabled',false);

      if( nStock <= 0 )
        $(opts.stockSubBtnSelector).prop('disabled',true);
      else
        $(opts.stockSubBtnSelector).prop('disabled',false);
    };

    return {
      PartDetails: {
        setup: function(opts) {
          var defaults = {
            nameTextSelector: '[name=showName]',
            nameEditBtnSelector: '[name=editName]',
            deleteBtnSelector: '[name=deletePart]',

            storeLocationTextSelector: '[name=showStoreloc]',
            storeLocationEditBtnSelector: '[name=editStoreloc]',

            footprintTextSelector: '[name=showFootprint]',
            footprintEditBtnSelector: '[name=editFootprint]',
            footprintImageSelector: '[name=imgFootprint]',

            supplierTextSelector: '[name=showSupplier]',
            supplierEditBtnSelector: '[name=editSupplier]',
            supplierImageSelector: '[name=imgSupplier]',

            partNumberTextSelector: '[name=showPartNumber]',
            partNumberEditBtnSelector: '[name=editPartNumber]',

            priceTextSelector: '[name=showPrice]',
            priceEditBtnSelector: '[name=editPrice]',

            totalTextSelector: '[name=showTotal]',
            totalEditBtnSelector: '[name=editTotal]',
            totalAddBtnSelector: '[name=addTotal]',
            totalSubBtnSelector: '[name=subTotal]',

            stockTextSelector: '[name=showStock]',
            stockEditBtnSelector: '[name=editStock]',
            stockAddBtnSelector: '[name=addStock]',
            stockSubBtnSelector: '[name=subStock]',

            minStockTextSelector: '[name=showMinStock]',
            minStockEditBtnSelector: '[name=editMinStock]',
            minStockAddBtnSelector: '[name=addMinStock]',
            minStockSubBtnSelector: '[name=subMinStock]',

            descriptionTextSelector: '[name=showDescription]',
            descriptionEditBtnSelector: '[name=editDescription]',

            pictureListViewSelector: '[name=partPictureListView]',
            pictureAddBtnSelector: '[name=pictureContainerAddButton]',
            pictureElementSelector: '[name=pictureContainer]',
            pictureAddElementSelector: '[name=pictureContainer][value=add]',
            pictureElDeleteBtnSelector: 'button[name=deletePicture]',
            pictureElMasterBtnSelector: 'input[altname=masterPicCheckbox]',
            pictureNodeTemplate: '',
            partImageElSelector: 'img.partimage',

            datasheetListViewSelector: '[name=partDatasheetListView]',
            datasheetAddBtnSelector: '[name=datasheetContainerAddButton]',
            datasheetElementSelector: '[name=datasheetContainer]',
            datasheetAddElementSelector: '[name=datasheetContainer][value=add]',
            datasheetElDeleteBtnSelector: 'button[name=deleteDatasheet]',
            datasheetNodeTemplate: '',

            partId: null
          };

          opts = $.extend({}, defaults, opts);

          _updateButtons(opts);

          $(opts.datasheetAddBtnSelector).click( function(e) {
      			// Show upload image dialog
            debugger;
      			sdb.GUI.Popup.openExternalPopup({
      				url: sdb.Core.basePath+'pages/popup-uploadfile.php',
      				forceReload: true,
      				fixedMaxWidth: '600px',
      				postdata: {
      					id: opts.partId,
      		    	method: 'addDatasheet',
      					itemtype: 'part',
      		   		type: 'datasheet'
      				},
      				customEventName: 'positiveResponse',
      				customEventHandler: function( e, data ) {
      					if( data && data.success ) {
      						// Dynamically add picture to list (before plus button)
      						// data.imageFileName
      						// data.pictureId
      						// data.thumbFileName
      						_addDatasheetContainer(opts, data.datasheetId, data.datasheetFullPath);
      					}
      				}
      			});
      		});

          $(opts.pictureAddBtnSelector).click( function(e) {
      			// Show upload image dialog
      			sdb.GUI.Popup.openExternalPopup({
      				url: sdb.Core.basePath+'pages/popup-uploadfile.php',
      				forceReload: true,
      				fixedMaxWidth: '600px',
      				postdata: {
      					id: opts.partId,
      		    	method: 'addPicture',
      					itemtype: 'part',
      		   		type: 'picture'
      				},
      				customEventName: 'positiveResponse',
      				customEventHandler: function( e, data ) {
      					if( data && data.success ) {
      						// Dynamically add picture to list (before plus button)
      						// data.imageFileName
      						// data.pictureId
      						// data.thumbFileName
      						_addPictureContainer(opts, data.pictureId, data.imageFullPath, data.imageFullPath);
      					}
      				}
      			});
      		});

      		$(opts.pictureListViewSelector).on('click',opts.pictureElDeleteBtnSelector, function(e) {

      			var id = $(this).attr('value');
      			debugger;
      			sdb.GUI.Popup.confirmPopUp({
      		    header: Lang.get('editPartDeletePicture'),
      		    text: Lang.get('noUndoHint'),
      		    confirmButtonText: Lang.get('delete'),
      		    confirm: function() {
      					$.ajax({
      						url: sdb.Core.basePath+'lib/edit-part.php',
      						type: 'POST',
      						dataType: 'json',
      						data: {
      							id: opts.partId,
      							method: 'deletePicture',
      							pictureId: id
      						}
      					}).done(function(data) {
      						// Update gui
      						if( data && data.success ) {

      							$('[name=pictureContainer][value='+data.pictureId+']').remove();
      							// Update main picture
      							$.ajax({
      								url: sdb.Core.basePath+'lib/json.parts.php',
      								type: 'POST',
      								dataType: 'json',
      								data: {
      									partid: opts.partId,
      									getDetailed: true
      								}
      							}).done(function(data) {
      								$(opts.partImageElSelector).attr('src',data.mainPicFile);
      								$(opts.partImageElSelector).attr('data-other-src',data.mainPicFile);
      							});
      						}
      					});
      				}
      			});
      		});

      		$(opts.pictureListViewSelector).on('change',opts.pictureElMasterBtnSelector, function (e) {
      			this.checked = !this.checked;

      			e.preventDefault();
      			e.stopPropagation();

      			if( this.checked ) {
              // Do not allow unselecting
              return false;
            }

      			var that = this;

      			$.ajax({
      				url: sdb.Core.basePath+'lib/edit-part.php',
      				type: 'POST',
      				dataType: 'json',
      				data: {
      					id: $(this).attr('value'),
      					method: 'setMasterPic'
      				}
      			}).done(function(data) {
      				// Update gui
      				if( data && data.success ) {
      					// Remove checks from all other checkboxes and check own
      					$(opts.pictureElMasterBtnSelector).closest('label').removeClass('active');
      					$(opts.pictureElMasterBtnSelector).prop('checked', false);
      					$(that).prop('checked', true);
                $(that).closest('label').addClass('active');

      					// Update main image right away
      					$(opts.partImageElSelector).attr('src',$('#picture-'+data.id).attr('src'));
      					$(opts.partImageElSelector).attr('data-other-src',$('#picture-'+data.id).attr('data-other-src'));
      				}
      			});

      		});

      		$(opts.supplierTextSelector).click(function(evt) {
      			var url = $(this).attr('url');
      			if( url )
      				window.open(url,'_blank');
      		});

      		$(opts.partNumberEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.inputPopUp({
      					header: Lang.get('editPartPartNumber'),
      					headline: Lang.get('editPartChangePartNumber'),
      					textPlaceholder: Lang.get('enterPartNumber'),
      					textDefault: $(opts.partNumberTextSelector).val(),
      					ok: function( newnumber ) {
      						// Apply new data in database
      						sdb.Part.editPartFieldData( opts.partId, 'supplierpartnr', newnumber,
      							function(data) {
      								if( data && data.success ) {
      									$(opts.partNumberTextSelector).val(newnumber);
      									// Get url for part and update picture
      									//
      									sdb.Supplier.getSupplierByIdAsync({
      										id: $(opts.supplierTextSelector).attr('supplierId'),
      										partNr: newnumber,
      										done: function(data) {
      											// Update gui
      											if( data ) {
      												$(opts.supplierTextSelector).attr('url',data.urlTemplate);
      											}
      										}
      									});
      								}
      							}
      						);
      					}
      			});
      		});

      		$(opts.nameEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.inputPopUp({
      		    header: Lang.get('editPartNewName'),
      		    headline: Lang.get('editPartChangeName'),
      		    textPlaceholder: Lang.get('enterName'),
      		    textDefault: $(opts.nameTextSelector).text(),
      		    ok: function( newName ) {
      					// Apply new data in database
      					sdb.Part.editPartFieldData( opts.partId, 'name', newName,
      						function(data) {
      							if( data && data.success ) {
      								$(opts.nameTextSelector).text(newName);
      							}
      						}
      					);
      				}
      			});
      		});

      		$(opts.deleteBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.confirmPopUp({
      		    header: Lang.get('editPartDelete'),
      		    text: Lang.get('noUndoHint'),
      		    confirmButtonText: Lang.get('delete'),
      		    confirm: function() {
      					// TODO Submit and delete
      					alert('TODO Delete part');
      				}
      			});
      		});

      		$(opts.storeLocationEditBtnSelector).click(function(evt) {
            debugger;
            // This is the function that sets the parts store location to the
            // specified id, name
            var setStoreLocation = function( id, name, done ) {
              sdb.Part.editPartFieldData( opts.partId, 'storelocation', id,
                function(data) {
                  if( data && data.success ) {
                    // Load store location name and store in database
                    $(opts.storeLocationTextSelector).attr('value',name);
                    if( done ) done(data);
                  }
                }
              );
            };

      			sdb.GUI.Popup.openExternalPopup({
      				forceReload: true,
      				url: sdb.Core.basePath+'pages/popup-selectstorelocation.php',
      				afteropen: function(evt) {
      					$(evt.target).find("input").first().focus().select();
      				},
      				afterclose: function(evt) {
      					//alert(JSON.stringify(evt));
      				},
      				click: function(evt) {
                var $dialog = evt.data;
                debugger;

                if( $(evt.currentTarget)[0].id == "addButton" ) {
                    evt.preventDefault();
                    $dialog.modal('hide');
                    sdb.GUI.Popup.inputPopUp({
                      message: Lang.get('editStoreLocationAddHint'),
                      headline: Lang.get('editStoreLocationNewName'),
                      textLabel: Lang.get('editStoreLocationAdd'),
                      textPlaceholder: Lang.get('namePlaceholder'),
                      ok: function(val) {
                        // Cerate new storage location and use it!
                        sdb.StoreLocation.createStoreLocationAsync({
                          name: val,
                          done: function(data) {
                            if( data.success ) {
        						           setStoreLocation(data.id, data.name, function(data) {
                                 if( !data || !data.success ) {
                                   $dialog.modal('show');
                                 }
                               })
                            } else {
                              $dialog.modal('show');
                            }
                          }
                        });
                      },
                      cancel: function(val) {
                        $dialog.modal('show');
                      }
                    });
                    return;
                }
                else if( $(evt.currentTarget).attr("buttonresult") != "ok" )
                  return;

                evt.preventDefault();

      					var storeClicked = $dialog.find('input[name=listItemOptions]:checked');

      					if( storeClicked.length > 0 )
      					{
                  var storeId   = storeClicked.val();
                  var storeName = storeClicked.parent().find('h6[name=nameListItem]').text();
                  setStoreLocation( storeId, storeName, function(data) {
                    if( data && data.success )
                      $dialog.modal('hide');
                  } );
      					}
      				}
      			});
      		});

      		$(opts.footprintEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.openExternalPopup({
      				forceReload: true,
      				url: sdb.Core.basePath+'pages/popup-selectfootprint.php',
      				afteropen: function(evt) {
      					$(evt.target).find("input").first().focus().select();
      				},
      				afterclose: function(evt) {
      				},
      				click: function(evt) {
                debugger;

                if( $(evt.currentTarget).attr("buttonresult") != "ok" ) return;

                evt.preventDefault();

                var $dialog = evt.data;
      					var fpClicked = $dialog.find('input[name=listItemOptions]:checked');

      					if( fpClicked.length > 0 )
      					{
                  var fpId = fpClicked.val();
      						sdb.Part.editPartFieldData( opts.partId, 'footprint', fpId,
      							function(data) {
      								if( data && data.success ) {
      									// Load store location name and store in database
                        var fpName = fpClicked.parent().find('h6[name=nameListItem]').text();
      									$(opts.footprintTextSelector).attr('value',fpName);
      									// Update pictures
      									$.ajax({	// Main picture if necessary
      										url: sdb.Core.basePath+'lib/json.parts.php',
      										type: 'POST',
      										dataType: 'json',
      										data: {
      											partid: opts.partId,
      											getDetailed: true
      										}
      									}).done(function(data) {
      										// Update gui
      										if( data ) {
      											var imgFile = data.mainPicFile;
      											$(opts.partImageElSelector).attr({
      												src: imgFile,
      												'data-other-src': imgFile
      											});
      										}
      									});

       									$.ajax({ // Update footprint picture
      										url: sdb.Core.basePath+'lib/json.footprints.php',
      										type: 'POST',
      										dataType: 'json',
      										data: {
      											id: fpId
      										}
      									}).done(function(data) {
      										// Update gui
      										if( data ) {
      											var imgFile = data['imgPath']; //sdb.Core.basePath+'img/footprint/'+
      											$(opts.footprintImageSelector).attr({
      												src: imgFile,
      												'data-other-src': imgFile
      											});
      										}
      									});

                        $dialog.modal('hide');
      								}
      							}
      						);
      					}
      				}
      			});
      		});

      		$(opts.supplierEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.openExternalPopup({
      				forceReload: true,
      				url: sdb.Core.basePath+'pages/popup-selectsupplier.php',
      				afteropen: function(evt) {
      					$(evt.target).find("input").first().focus().select();
      				},
      				afterclose: function(evt) {
      				},
      				click: function(evt) {
                debugger;

                if( $(evt.currentTarget).attr("buttonresult") != "ok" ) return;

                evt.preventDefault();

                var $dialog = evt.data;
      					var spClicked = $dialog.find('input[name=listItemOptions]:checked');

      					if( spClicked.length > 0 )
      					{
                  var spId = spClicked.val();
      						sdb.Part.editPartFieldData( opts.partId, 'supplierid', spId,
      							function(data) {
      								if( data && data.success ) {
      									// Load store location name and store in database
                        var spName = spClicked.parent().find('h6[name=nameListItem]').text();
      									$(opts.supplierTextSelector)
      										.attr('value',spName)
      										.attr('supplierId', spId);

      									// Get url for part and update picture
      									$.ajax({
      										url: sdb.Core.basePath+'lib/json.suppliers.php',
      										type: 'POST',
      										dataType: 'json',
      										data: {
      											id: spId,
      											partNr: $(opts.partNumberTextSelector).val()
      										}
      									}).done(function(data) {
      										// Update gui
      										if( data ) {
      											var imgFile = data['imgPath'];
      											$(opts.supplierImageSelector).attr({
      												src: imgFile,
      												'data-other-src': imgFile
      											});

      											$(opts.supplierTextSelector).attr('url',data.urlTemplate);
                            $dialog.modal('hide');
      										}
      									});
      								}
      							}
      						);
      					}
      				}
      			});
      		});

      		$(opts.priceEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.inputPopUp({
      		    header: Lang.get('editPartPrice'),
      		    headline: Lang.get('editPartChangePrice'),
      		    textPlaceholder: Lang.get('enterPrice'),
      		    textDefault: $(opts.priceTextSelector).attr('floatvalue'),
      		    ok: function( newPrice ) {
      					sdb.Part.editPartFieldData( opts.partId, 'price', newPrice,
      						function(data) {
      							if( data && data.success ) {
      								// Submit and save new name, then update GUI on success
      								$(opts.priceTextSelector).val(data.pricetext);
      							}
      						}
      					);
      				},
      				validatorRules: {
      					required: true,
      	      	number: true,
      	      	min: 0.0
      				}
      			});
      		});

      		$(opts.totalEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.inputPopUp({
      				header: Lang.get('editPartTotal'),
      				headline: Lang.get('editPartChangeTotal'),
      				textPlaceholder: Lang.get('enterAmount'),
      				textDefault: $(opts.totalTextSelector).val(),
      				ok: function( total ) {
      					// Submit and save new name, then update GUI on success
      					sdb.Part.editPartFieldData( opts.partId, 'totalstock', total,
      						function(data) {
      							if( data && data.success ) {
      								$(opts.totalTextSelector).val(total);
      								_updateButtons(opts);
      							}
      						}
      					);
      				},
      				validatorRules: {
      					required: true,
      					digits: true,
      					min: parseInt($(opts.stockTextSelector).val())
      				}
      			});
      		});

      		$(opts.totalAddBtnSelector).click(function(evt) {
      			sdb.Part.incrementTotal(opts.partId, function(newval) {
      				$(opts.totalTextSelector).val(newval);
      				_updateButtons(opts);
      			});
      		});

      		$(opts.totalSubBtnSelector).click(function(evt) {
      			sdb.Part.decrementTotal(opts.partId, function(newval) {
      				$(opts.totalTextSelector).val(newval);
      				_updateButtons(opts);
      			});
      		});

      		$(opts.stockAddBtnSelector).click(function(evt) {
      			sdb.Part.incrementStock(opts.partId, function(newval) {
      				$(opts.stockTextSelector).val(newval);
      				_updateButtons(opts);
      			});
      		});

      		$(opts.stockSubBtnSelector).click(function(evt) {
      			sdb.Part.decrementStock(opts.partId, function(newval) {
      				$(opts.stockTextSelector).val(newval);
      				_updateButtons(opts);
      			});
      		});

      		$(opts.stockEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.inputPopUp({
      		    header: Lang.get('editPartStock'),
      		    headline: Lang.get('editPartChangeStock'),
      		    textPlaceholder: Lang.get('enterAmount'),
      		    textDefault: $(opts.stockTextSelector).val(),
      		    ok: function( stock ) {
      					// Submit and save new name, then update GUI on success
      					sdb.Part.editPartFieldData( opts.partId, 'instock', stock,
      						function(data) {
      							if( data && data.success ) {
      								$(opts.stockTextSelector).val(stock);
      								_updateButtons(opts);
      							}
      						}
      					);
      				},
      				validatorRules: {
      					required: true,
      	      	digits: true,
      					number: true,
      	      	min: 0,
      					max: parseInt($(opts.totalTextSelector).val())
      				}
      			});
      		});

      		$(opts.minStockEditBtnSelector).click(function(evt) {
      			sdb.GUI.Popup.inputPopUp({
      		    header: Lang.get('editPartMinStock'),
      		    headline: Lang.get('editPartChangeMinStock'),
      		    textPlaceholder: Lang.get('enterAmount'),
      		    textDefault: $(opts.minStockTextSelector).val(),
      		    ok: function( minstock ) {
      					// Submit and save new name, then update GUI on success
      					sdb.Part.editPartFieldData( opts.partId, 'mininstock', minstock,
      						function(data) {
      							if( data && data.success ) {
      								$(opts.minStockTextSelector).val(minstock);
      							}
      						}
      					);
      				},
      				validatorRules: {
      					required: true,
      	      	digits: true,
      	      	min: 0
      				}
      			});
      		});

      		$(opts.descriptionEditBtnSelector).click(function(evt) {
      			evt.preventDefault();
      			evt.stopPropagation();

      			sdb.GUI.Popup.inputMultilinePopUp({
      	      header: Lang.get('editPartDescriptionEdit'),
      	      headline: Lang.get('editPartDescriptionEditHint'),
      	      textPlaceholder: Lang.get('enterDescription'),
      	      textDefault: $(opts.descriptionTextSelector).text(),
      	      ok: function( newdescription ) {
      					sdb.Part.editPartFieldData( opts.partId, 'comment', newdescription,
      						function(data) {
      							if( data && data.success ) {
      								// Submit and save new name, then update GUI on success
      								$(opts.descriptionTextSelector).text(newdescription);
      							}
      						}
      					);
      				}
      			});
      		});

          // Handle resizing of window
          var lastwidth = 9999;
      		$(window).on('resize.sdb.page', function() {
            console.log("Firing resize");
            var $pc = $(sdb.Core.PageLoader.defaults.pageContainerSelector);
            var $main = $('main');
            var pcWidth = $pc.innerWidth()
            var mainPadding = $main.innerWidth() - $main.width();
            var width = pcWidth - mainPadding - 5; // Gap of 5 pixels

      			if( width < 360 && lastwidth >= 360 ) {
      				$([opts.totalSubBtnSelector,opts.totalAddBtnSelector,opts.stockSubBtnSelector,opts.stockAddBtnSelector].join(',')).parent().hide();
      			} else if( width >= 360 && lastwidth < 360) {
      				$([opts.totalSubBtnSelector,opts.totalAddBtnSelector,opts.stockSubBtnSelector,opts.stockAddBtnSelector].join(',')).parent().show();
      			}

      			if( width < 420 && lastwidth >= 420 ) {

      			} else if( width >= 420 && lastwidth < 420 ) {

      			}
      			lastwidth = width;

      		});

          // Detach resize handler
          $(document).one("pagebeforeload", function() {
            $(window).off('resize.sdb');
          });

          // Initial column hide/show
          $(window).triggerHandler('resize');
        }
      },
      SubCategoryTree: {
        setup: function(opts) {

          var defaults = {
            treeSelector: '#subcattree',
    				mainTreeSelector: '#navCategorytree',
            currentCategoryId: null
          };

          opts = $.extend(true, {}, defaults, opts);

          var $tree = $(opts.mainTreeSelector);
          var $subtree = $(opts.treeSelector);

          // Tree callback
          $subtree.bind('tree.init', function(e) {
            $subtree.tree('openNode',$subtree.tree('getTree').children[0]);
          });

          $subtree.tree();

          $subtree.bind('tree.click', function(e) {
            // e.node.name - Name string
            // e.node.id   - ID string
            sdb.Core.PageLoader.load({
              url: sdb.Core.basePath + 'pages/page-showparts.php?catid=' + e.node.id + '&showSubcategories=' + Number(e.node.children.length > 0)
            });
            //$('body').pagecontainer('change',);

            $tree.tree( 'selectNode', $tree.tree('getNodeById', e.node.id) );
          });

          // Select current category in tree
          if( opts.currentCategoryId ) {
            $tree.tree( 'selectNode', $tree.tree('getNodeById', opts.currentCategoryId) );
          }
        }
      },
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
            guiStyle: 'bootstrap',
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

          // Handle resizing of page
          var lastwidth = 9999;
          $(window).on('resize.sdb.page', function() {
            console.log("Firing resize");
            // Get width of pageContainer including padding
            var $pc = $(sdb.Core.PageLoader.defaults.pageContainerSelector);
            var $main = $('main');
            var pcWidth = $pc.innerWidth()
            var mainPadding = $main.innerWidth() - $main.width();
            var width = pcWidth - mainPadding - 5; // Gap of 5 pixels

            if( width < 520 && lastwidth >= 520 ) {
              $(opts.listSelector).jqGrid('hideCol',['mininstock'/*,'datasheet'*/]);
            } else if( width >= 520 && lastwidth < 520) {
              $(opts.listSelector).jqGrid('showCol',['footprint','mininstock'/*,'datasheet'*/]);
            }

            if( width < 420 && lastwidth >= 420 ) {
              $(opts.listSelector).jqGrid('hideCol','footprint');
              $('#'+opts.groupingSwitch.id+'_super').hide();
            } else if( width >= 420 && lastwidth < 420 ) {
              $('#'+opts.groupingSwitch.id+'_super').show();
              $(opts.listSelector).jqGrid('showCol','footprint');
            }
            lastwidth = width;

            $(opts.listSelector).jqGrid('setGridWidth', width);
          });

          // Detach resize handler
          $(document).one("pagebeforeload", function() {
            $(window).off('resize.sdb');
          });

          // Initial column hide/show
          $(window).triggerHandler('resize');
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
