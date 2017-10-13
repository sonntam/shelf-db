// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

    var editCategoryModule = (function(){

    var _ctrlBoxSelector = undefined;

    var hideControls = function() {
      var $ctrl = $(_ctrlBoxSelector);

      $ctrl.toggleClass('editcontrols-hidden', true);
      $ctrl.attr("data-id",null);
    };

    return {
      hideControls: hideControls,

      CategoryTree: {
        setup: function(opts) {

          defaults = {
            ctrlBoxSelector: '.editcontrols',
            treeSelector: '#editcategorytree',
            btnAddRootSelector: '#editcontrols-addroot',
            btnEditSelector: '#editcontrols-edit',
            btnDeleteSelector: '#editcontrols-delete',
            btnAddSelector: '#editcontrols-add',
          };

          opts = $.extend({}, defaults, opts);

          _ctrlBoxSelector = opts.ctrlBoxSelector;

          var $tree       = $(opts.treeSelector);
          var $ctrl       = $(opts.ctrlBoxSelector);
          var $btnAddRoot = $(opts.btnAddRootSelector);
          var $btnAdd     = $(opts.btnAddSelector);
          var $btnEdit    = $(opts.btnEditSelector);
          var $btnDelete  = $(opts.btnDeleteSelector);
          var $mainTree   = sdb.GUI.Nav.CategoryTree.getElement();

          // Create tree
    			$tree.tree({
    				dragAndDrop: true
    			});

          // Attach events

    			// Style empty categories
    			$tree.bind('tree.init', function(){
    				var tree = $tree.tree('getTree');
    				tree.iterate(function(node) {
    					if (node.partcount == 0) {
    						$(node.element).find("span").first().addClass('jqtree-empty');
    					}
    					return true;
    				});
    			});

    			// Stop clicks from passing through to the parents
    			$ctrl.click(function (evt) {
    			    evt.stopPropagation();
    			});

    			// Add root category
    			$btnAddRoot.click(function(evt) {
    				evt.stopPropagation();

    				ShelfDB.GUI.Popup.inputPopUp({
    			    header: Lang.get('editCategoryAdd'),
    			    headline: Lang.get('editCategoryAdd'),
    			    message: Lang.get('editCategoryAddRootHint'),
    			    confirmButtonText: Lang.get('add'),
    			    textLabel: Lang.get('name')+":",
    			    textPlaceholder: Lang.get('name'),
    			    ok: function(value){
    						// Set name AJAX call to mysql script
    						$.ajax({
    							url: sdb.Core.basePath + 'lib/edit-category.php',
    							type: 'POST',
    							dataType: 'json',
    							data: {
    								method: 'add',
    								newname: value,
    								parentid: 0
    							}
    						}).done(function(data) {

    							if( data['success'] == true )
    							{
    									// Open tree to new node (but do not select it)
    									$ctrl.detach();
    									$tree.tree('prependNode', {
    										name: data['newname'],
    										id: data['newid'],
    										partcount: 0
    									} );
    									$tree.trigger('tree.init');

    									//$tree.tree('openNode', node);
    									//$ctrl.prependTo($(node.element).find("span").first());

    									// Update main tree
    									$mainTree.tree('reload');
    							}
    						});
    					}
    				});
    			});

    			$btnEdit.click(function (evt) {
    				evt.stopPropagation();

    				var id      = $ctrl.attr("data-id");
    				var node    = $tree.tree('getNodeById', id)
    				var curname = node.name;

    				ShelfDB.GUI.Popup.inputPopUp({
    			    header: Lang.get('editCategoryChange'),
    			    headline: Lang.get('editCategoryNewName'),
    			    confirmButtonText: Lang.get('change'),
    			    textLabel: Lang.get('name')+":",
    			    textPlaceholder: Lang.get('name'),
    			    textDefault: curname,
    			    ok: function(value){

    						if( value != null && value != curname ){
    							// Set name AJAX call to mysql script
    							$.ajax({
    								url: ShelfDB.Core.basePath + 'lib/edit-category.php',
    								type: 'POST',
    								dataType: 'json',
    								data: {
    									method: 'editName',
    									newname: value,
    									id: id
    								}
    							}).done(function(data) {

    								if( data['success'] == true )
    								{
    										// Change name in tree
    										$ctrl.detach();
    										$tree.tree('updateNode', node, data['newname'] );
    										$ctrl.prependTo($(node.element).find("span").first());
    										$tree.trigger('tree.init');

    										// Update main tree!
    										$mainTree.tree('reload');
    								}

    							});
    						}
    					}
    				});
    			});

    			$btnAdd.click(function (evt) {
    				evt.stopPropagation();

    				var id      = $ctrl.attr("data-id");
    				var node    = $tree.tree('getNodeById', id)

    				ShelfDB.GUI.Popup.inputPopUp({
    			    header: Lang.get('editCategoryAdd'),
    			    headline: Lang.get('editCategoryNewName'),
    			    message: (Lang.get('editCategoryAddHint'))(node.name),
    			    confirmButtonText: Lang.get('add'),
    			    textLabel: Lang.get('name')+":",
    			    textPlaceholder: Lang.get('name'),
    			    ok: function(value){
    						// Set name AJAX call to mysql script
    						$.ajax({
    							url: ShelfDB.Core.basePath + 'lib/edit-category.php',
    							type: 'POST',
    							dataType: 'json',
    							data: {
    								method: 'add',
    								newname: value,
    								parentid: id
    							}
    						}).done(function(data) {

    							if( data['success'] == true )
    							{
    									// Open tree to new node (but do not select it)
    									$ctrl.detach();
    									$tree.tree('appendNode', {
    										name: data['newname'],
    										id: data['newid'],
    										partcount: 0
    									} , node);
    									$tree.trigger('tree.init');

    									$tree.tree('openNode', node);
    									$ctrl.prependTo($(node.element).find("span").first());

    									// Update main tree
    									$mainTree.tree('reload');
    							}
    						});
    					}
    				});
    			});

    			$btnDelete.click(function (evt) {
    				evt.stopPropagation();

    				var id      = $ctrl.attr("data-id");
    				var node    = $tree.tree('getNodeById', id)
    				var curname = node.name;

    				// Check if category has parts
    				var partmovestring = "";
    				if( node.partcount > 0 )
    				{
    					partmovestring = " "+(Lang.get('editCategoryRemoveLeafHint'))(node.parent.name);
    				}

    				// Check if category has children
    				var subcatstring = "";
    				if( node.children.length > 0 ) {
    					var subcatstring = (Lang.get('editCategoryRemoveSubLeavesHint'))(node.children.map(function(el){
    							return "\""+el.name+"\"";
    						}).join(", "));
    				}

    				ShelfDB.GUI.Popup.confirmPopUp({
    			    header: Lang.get('editCategoryDelete'),
    			    text: (Lang.get('editCategoryRemoveLeafQuestion'))(curname, subcatstring, partmovestring ),
    			    confirmButtonText: Lang.get('delete'),
    			    confirm: function() {

    						// Set name AJAX call to mysql script
    						$.ajax({
    							url: ShelfDB.Core.basePath + 'lib/edit-category.php',
    							type: 'POST',
    							dataType: 'json',
    							data: {
    								method: 'delete',
    								id: id
    							}
    						}).done(function(data) {

    							if( data['success'] == true )
    							{
    									// Open tree to new node (but do not select it)
    									$ctrl.detach();
    									$tree.tree('removeNode', node);
    									$tree.trigger('tree.init');

    									// Hide controls
    									sdb.GUI.EditCategory.hideControls();

    									// Update main tree
    									//$maintree.tree('removedNode', $maintree.tree('getNodeById', data['id']) );
    									$mainTree.tree('reload');
    							}
    						});
    					}
    				});
    			});

    			$tree.bind('tree.move', function(event) {
    				event.preventDefault();

    				$ctrl.detach();
    				sdb.GUI.EditCategory.hideControls();

    				/*
    				console.log('moved_node', event.move_info.moved_node);
    				console.log('target_node', event.move_info.target_node);
    				console.log('position', event.move_info.position);
    				console.log('previous_parent', event.move_info.previous_parent);
    				*/

    				var node   = event.move_info.moved_node;
    				var pnode  = node.parent;
    				var tnode  = event.move_info.target_node;
    				var npnode = (event.move_info.position == "inside" ? tnode : tnode.parent );

    				var prevParentId = (pnode.name == "" ? 0 : pnode.id);
    				var newParentId  = (npnode.name == "" ? 0 : npnode.id);

    				if( prevParentId != newParentId )
    				{
    					ShelfDB.GUI.Popup.confirmPopUp({
    				    header: Lang.get('editCategoryMove'),
    				    text: (Lang.get('editCategoryMoveConfirmTest'))(node.name,npnode.name),
    				    confirmButtonText: Lang.get('move'),
    				    confirm: function() {
    							// Apply move in database
    							$.ajax({
    								url: ShelfDB.Core.basePath + 'lib/edit-category.php',
    								type: 'POST',
    								dataType: 'json',
    								data: {
    									method: 'movecat',
    									id: node.id,
    									newparentid: newParentId
    								}
    							}).done(function(data) {

    								if( data['success'] == true )
    								{
    										// Update main tree
    										//$maintree.tree('removedNode', $maintree.tree('getNodeById', data['id']) );
    										$mainTree.tree('reload');

    										// Visual move
    										event.move_info.do_move();

    										// Update counts
    										if( pnode.name != "" ) pnode.partcount -= node.partcount;
    										if( npnode.name != "" ) npnode.partcount += node.partcount;

    										// Update tree
    										$tree.trigger('tree.init');
    								}
    							});
    						}
    					});
    				}
    			});

    			$tree.bind('tree.select', function(event) {

    				var node = event.node;

    				if( node ) { // Selection
    					// Move controls to correct position
    					//debugger;
    					//alert($(node.element).textWidth());

    					// Show controls/Hide them for the previously selected node
    					if( node.partcount == 0 ) {
    						$btnDelete.parent().show();
    					} else {
    						if( node.children.length == 0 ) {
    							$btnDelete.parent().show();
    						} else {
    							$btnDelete.parent().hide();
    						}
    					}
    					$ctrl.toggleClass('editcontrols-hidden', false);
    					$ctrl.detach().prependTo($(node.element).find("span").first());
    					$ctrl.attr("data-id",node.id);

    					//alert("Selected id = "+event.node.id+" Previous id = "+event.deselected_node.id);
    				} else {
    					//alert("Deselected id = "+event.previous_node.id);
    					sdb.GUI.EditCategory.hideControls();
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
    EditCategory: editCategoryModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
