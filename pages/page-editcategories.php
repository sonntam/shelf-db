<?php
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');
?>

<script type="text/javascript">
    window.location="index.php#page-showparts?catid=1";
</script>

<div id=showparts data-role="page">

	<script>
		pageHookClear();

		$.mobile.pageContainerBeforeShowTasks.push( function(event,ui) {
			console.log("DEBUG: pageTask");

			var $tree = $('#editcategorytree');
			var $ctrl = $(".editcontrols");

			$tree.tree({
				dragAndDrop: true
			});

			// Style empty categories
			$tree.bind('tree.init',function(){
				var tree = $tree.tree('getTree');
				tree.iterate(function(node) {
					if (node.partcount == 0) {
						$(node.element).find("span").first().addClass('jqtree-empty');
					}
					return true;
				});
			});

			function hideControls() {
				$ctrl.toggleClass('editcontrols-hidden', true);
				$ctrl.attr("data-id",null);
			}

			// Stop clicks from passing through to the parents
			$('.editcontrols').click(function (evt) {
			    evt.stopPropagation();
			});

			// Add root category
			$('#editcontrols-addroot').click(function(evt) {
				evt.stopPropagation();

				inputPopUp(Lang.get('editCategoryAdd'), Lang.get('editCategoryAdd'),
					Lang.get('editCategoryAddRootHint'), Lang.get('add'),
					Lang.get('name')+":", Lang.get('name'), "", function(value){
						// Set name AJAX call to mysql script
						$.ajax({
							url: '/lib/edit-category.php',
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
									var $maintree = $('#categorytree');
									$maintree.tree('reload');
							}
						});
					}
				);
			});

			$('#editcontrols-edit').click(function (evt) {
				evt.stopPropagation();

				var id      = $ctrl.attr("data-id");
				var node    = $tree.tree('getNodeById', id)
				var curname = node.name;

				inputPopUp(Lang.get('editCategoryChange'), Lang.get('editCategoryNewName'), "", Lang.get('change'),
					Lang.get('name')+":", Lang.get('name'), curname, function(value){

						if( value != null && value != curname ){
							// Set name AJAX call to mysql script
							$.ajax({
								url: '/lib/edit-category.php',
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

										// Update main tree!
										var $maintree = $('#categorytree');
										$maintree.tree('reload');
								}

							});
						}
					}
				);

			});

			$('#editcontrols-add').click(function (evt) {
				evt.stopPropagation();

				var id      = $ctrl.attr("data-id");
				var node    = $tree.tree('getNodeById', id)

				inputPopUp( Lang.get('editCategoryAdd'), Lang.get('editCategoryNewName'),
					(Lang.get('editCategoryAddHint'))(node.name), Lang.get('add'),
					Lang.get('name')+":", Lang.get('name'), "", function(value){
						// Set name AJAX call to mysql script
						$.ajax({
							url: '/lib/edit-category.php',
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
									var $maintree = $('#categorytree');
									$maintree.tree('reload');
							}
						});
					}
				);
			});

			$('#editcontrols-delete').click(function (evt) {
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

				confirmPopUp({
			    header: Lang.get('editCategoryDelete'),
			    text: (Lang.get('editCategoryRemoveLeafQuestion'))(curname, subcatstring, partmovestring ),
			    confirmButtonText: Lang.get('delete'),
			    confirm: function() {

						// Set name AJAX call to mysql script
						$.ajax({
							url: '/lib/edit-category.php',
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
									$tree.tree('removedNode', node);

									// Hide controls
									hideControls();

									// Update main tree
									var $maintree = $('#categorytree');
									//$maintree.tree('removedNode', $maintree.tree('getNodeById', data['id']) );
									$maintree.tree('reload');
							}
						});
					}
				});
			});

			$tree.bind('tree.move', function(event) {
				event.preventDefault();

				$ctrl.detach();
				hideControls();

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
					confirmPopUp({
				    header: Lang.get('editCategoryMove'),
				    text: (Lang.get('editCategoryMoveConfirmTest'))(node.name,npnode.name),
				    confirmButtonText: Lang.get('move'),
				    confirm: function() {
							// Apply move in database
							$.ajax({
								url: '/lib/edit-category.php',
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
										var $maintree = $('#categorytree');
										//$maintree.tree('removedNode', $maintree.tree('getNodeById', data['id']) );
										$maintree.tree('reload');

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
						$('#editcontrols-delete').parent().show();
					} else {
						if( node.children.length == 0 ) {
							$('#editcontrols-delete').parent().show();
						} else {
							$('#editcontrols-delete').parent().hide();
						}
					}
					$ctrl.toggleClass('editcontrols-hidden', false);
					$ctrl.detach().prependTo($(node.element).find("span").first());
					$ctrl.attr("data-id",node.id);

					//alert("Selected id = "+event.node.id+" Previous id = "+event.deselected_node.id);
				} else {
					//alert("Deselected id = "+event.previous_node.id);
					hideControls();
				}
			});



		});

	</script>

  <div data-role="header">
    <h1 uilang="editCategories"></h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>
		<button id="editcontrols-addroot" class="ui-icon-fa-plus ui-btn-inline ui-btn ui-btn-right ui-btn-icon-notext" uilang="add"></button>
  </div>
  <div role="main" class="ui-content">

			<h3 uilang="categories"></h3>
			<ul>
			<li uilang="editCategoriesDragDropHint"></li>
			<li uilang="editCategoriesClickNodeHint"></li>
			<li uilang="editCategoriesDeleteHint"></li>
			<li uilang="editCategoriesDeleteMigrateHint"></li>
			<li uilang="editCategoriesRootDeleteHint"></li>
			</ul>

			<div id="editcategorytree" data-url="/lib/json.categorytree.php"></div>
			<div class="editcontrols editcontrols-hidden">
				<span><i id="editcontrols-edit" class="fa fa-pencil"></i>&nbsp;</span>
				<span><i id="editcontrols-delete" class="fa fa-times" style="color: darkred"></i>&nbsp;</span>
				<span><i id="editcontrols-add" class="fa fa-plus"  style="color: green"></i>&nbsp;</span>&nbsp;
			</div>
			<div id="dialog"></div>
  </div>



  <div data-role="footer">
    <?php include(__DIR__.'/page-footer.php'); ?>
  </div>
</div>
