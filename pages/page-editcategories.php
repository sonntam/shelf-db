<?php
	require_once(dirname(__DIR__).'/classes/partdatabase.class.php');
?>

<script type="text/javascript">
    window.location="index.php#page-showparts?catid=1";
</script>

<div id=showparts data-role="page">

	<script>
		$(':mobile-pagecontainer').off('pagecontainerbeforeshow');
		$(':mobile-pagecontainer').on('pagecontainerbeforeshow', function(event,ui) {
			console.log("DEBUG: pagecontainer - beforeshow");

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

			function confirmPopUp(header, text, confirmbtntext, fnc_ok, fnc_cancel) {

				var $buttonresult = "";
				var $popup = $('#popupDialog');
				var $cancelbtn = $popup.find("[name='popupCancelBtn']");

				$popup.find("[name='dialogHeader']").first().text(header);
				$popup.find("[name='dialogText']").first().text(text);
				$popup.find("[name='popupOkBtn']").first().text(confirmbtntext);

				// Keypress handlers
				$popup.off('keypress');
				$popup.on('keypress', function(e){
					if(e.keyCode == 13) {
						// Submit
						e.stopPropagation();
						//$okbtn.trigger("click");
					}
				});

				$popup.off('keyup');
				$popup.on('keyup', function(e){
					if(e.keyCode == 27) {
						e.stopPropagation();
						$cancelbtn.trigger("click");
					}
				});

				// Button click handlers
				$popup.find('a').one('click', function(ev){
					// Return buttonresult
					$buttonresult = $(ev.target).attr('buttonresult');
					console.log($buttonresult);
					$popup.find('a').off('click');
				});

				$popup.one('popupafteropen', function(ev,ui) {
						$cancelbtn.focus();
				});
				$popup.popup('open', { transition: "pop"});
				$popup.one('popupafterclose', function(ev,ui) {

					if( $buttonresult == "ok" && fnc_ok ) {
						fnc_ok();
					} else if ($buttonresult == "cancel" && fnc_cancel) {
						fnc_cancel();
					}
				});
			}

			function inputPopUp(header, headline, message, confirmbtntext,
				textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel) {

				var $buttonresult = "";
				var $popup = $('#popupInputDialog');
				var $input = $popup.find("[name='dialogText']");
				var $okbtn = $popup.find("[name='popupOkBtn']");
				var $cancelbtn = $popup.find("[name='popupCancelBtn']");

				$popup.find("[name='dialogHeader']").first().text(header);
				$popup.find("[name='dialogHeadline']").first().text(headline);
				$popup.find("[name='dialogMessage']").first().text(message);
				$popup.find("[name='dialogTextLabel']").first().text(textlabel);
				$input.val(textdefault);
				$input.attr('placeholder',textplaceholder);
				$okbtn.text(confirmbtntext);

				// Keypress handlers
				$input.off('keypress');
				$input.on('keypress', function(e){
					if(e.keyCode == 13) {
						// Submit
						e.stopPropagation();
						$okbtn.trigger("click");
					}
				});

				$input.off('keyup');
				$input.on('keyup', function(e){
					if(e.keyCode == 27) {
						e.stopPropagation();
						$cancelbtn.trigger("click");
					}
				});

				// Button click handlers
				$popup.find('a').one('click', function(ev){
					// Return buttonresult
					$buttonresult = $(ev.target).attr('buttonresult');
					console.log($buttonresult);
					$popup.find('a').off('click');
				});

				$popup.one('popupafteropen', function(ev,ui) {
						$popup.find("input").first().focus().select();
				});
				$popup.popup('open', { transition: "pop"});
				$popup.one('popupafterclose', function(ev,ui) {
					if( $buttonresult == "ok" && fnc_ok ) {
						fnc_ok($input.val());
					} else if ($buttonresult == "cancel" && fnc_cancel) {
						fnc_cancel($input.val());
					}
				});
			}

			// Stop clicks from passing through to the parents
			$('.editcontrols').click(function (evt) {
			    evt.stopPropagation();
			});

			// Add root category
			$('#editcontrols-addroot').click(function(evt) {
				evt.stopPropagation();

				inputPopUp("Kategorie anlegen", "Neue Kategorie", "Namen für neue Wurzelkategorie eingeben", "Anlegen",
					"Name:", "Name", "", function(value){
						// Set name AJAX call to mysql script
						$.ajax({
							url: '/category-edit.php',
							type: 'POST',
							dataType: 'json',
							data: {
								method: 'addcat',
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

				inputPopUp("Kategorie ändern", "Neuer Name", "", "Ändern",
					"Name:", "Name", curname, function(value){

						if( value != null && value != curname ){
							// Set name AJAX call to mysql script
							$.ajax({
								url: '/category-edit.php',
								type: 'POST',
								dataType: 'json',
								data: {
									method: 'editcatname',
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

				inputPopUp("Kategorie anlegen", "Neue Kategorie", "Namen für neue Kategorie innerhalb von \""+node.name+"\" eingeben", "Anlegen",
					"Name:", "Name", "", function(value){
						// Set name AJAX call to mysql script
						$.ajax({
							url: '/category-edit.php',
							type: 'POST',
							dataType: 'json',
							data: {
								method: 'addcat',
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
				var $popup = $('#popupDialog');

				var id      = $ctrl.attr("data-id");
				var node    = $tree.tree('getNodeById', id)
				var curname = node.name;

				// Check if category has parts
				var partmovestring = "";
				if( node.partcount > 0 )
				{
					partmovestring = " Enthaltene Teile werden in die übergeordnete Kategorie \""+node.parent.name+"\" verschoben.";
				}

				// Check if category has children
				var subcatstring = "";
				if( node.children.length > 0 ) {
					var subcatstring = " sowie die Unterkategorien " + node.children.map(function(el){
							return "\""+el.name+"\"";
						}).join(", ");
				}

				confirmPopUp(
				 	"Kategorie löschen",
					"Möchten Sie die Kategorie \""+curname+"\"" + subcatstring + " wirklich löschen?" + partmovestring + " Diese Aktion kann nicht rückgängig gemacht werden.",
					"Löschen",
					function() {

						// Set name AJAX call to mysql script
						$.ajax({
							url: '/category-edit.php',
							type: 'POST',
							dataType: 'json',
							data: {
								method: 'deletecat',
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
				);
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
					confirmPopUp(
					 	"Kategorie verschieben",
						"Kategorie \""+node.name+"\" sowie alle Untergruppen nach \""+(npnode.name || "Wurzelebene")+"\" verschieben?",
						"Verschieben",
						function() {
							// Apply move in database
							$.ajax({
								url: '/category-edit.php',
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
					);
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
    <h1>Kategorie Bearbeiten</h1>
    <a href="#navpanel" class="ui-btn"><i class="fa fa-bars"></i></a>

  </div>
  <div role="main" class="ui-content">

			<h3>Kategorien</h3>
			<ul>
			<li>Drag &amp; Drop zum Verschieben von Knoten</li>
			<li>Anklicken eines Knotens zum Anzeigen weiterer Optionen</li>
			<li>Nur wenn eine Kategorie sowie die Unterkategorien keer sind, kann ein ganzer Zweig gelöscht werden.</li>
			<li>Wird eine nicht-leere Kategorie am Ende eines Baums gelöscht, werden die enthaltenen Elemente übergeordneten Ebene zugeordnet.</li>
			<li>Kategorien auf der Wurzelebene können nur gelöscht werden, wenn sie leer sind (grau)</li>
			</ul>
			<p><i id="editcontrols-addroot" class="fa fa-plus" style="color: green"></i></p>
			<div id="editcategorytree" data-url="categorytree.json.php"></div>
			<div class="editcontrols editcontrols-hidden">
				<span><i id="editcontrols-edit" class="fa fa-pencil"></i>&nbsp;</span>
				<span><i id="editcontrols-delete" class="fa fa-times" style="color: darkred"></i>&nbsp;</span>
				<span><i id="editcontrols-add" class="fa fa-plus"  style="color: green"></i>&nbsp;</span>&nbsp;
			</div>

			<div data-role="popup" id="popupDialog" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:500px;">
			    <div data-role="header" data-theme="a">
			    <h1 name="dialogHeader" style="margin: 0 15px;"></h1>
			    </div>
			    <div role="main" class="ui-content">
			        <h3 class="ui-title">Sind Sie sicher?</h3>
			    		<p name="dialogText"></p>
			        <a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Abbrechen</a>
			        <a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-a" data-rel="back" data-transition="flow">Ok</a>
			    </div>
			</div>

			<div data-role="popup" id="popupInputDialog" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:500px;">
			    <div data-role="header" data-theme="a">
			    <h1 name="dialogHeader" style="margin: 0 15px;"></h1>
			    </div>
			    <div role="main" class="ui-content">
			        <h3 name="dialogHeadline" class="ui-title"></h3>
			    		<p name="dialogMessage"></p>
							<label for="usertext" class="ui-hidden-accessible" name="dialogTextLabel"></label>
            	<input type="text" name="dialogText" value="" placeholder="" data-theme="a">
			        <a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Abbrechen</a>
			        <a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-a" data-rel="back" data-transition="flow">Ok</a>
			    </div>
			</div>
  </div>



  <div data-role="footer">
    <?php include(__DIR__.'pages/page-footer.php'); ?>
  </div>
</div>
