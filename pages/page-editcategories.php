<?php
	require_once(dirname(__DIR__).'/classes/shelfdb.class.php');
?>

<script type="text/javascript">
    window.location="<?php echo $pdb->RelRoot(); ?>index.php#<?php echo $_SERVER['REQUEST_URI']; ?>";
</script>

<div id=editcategories data-role="page">

	<script>
		$.mobile.pageContainerBeforeShowTasks.push( function(event,ui) {

			console.log("DEBUG: pageTask");

			// Setup the user controls
			ShelfDB.GUI.EditCategory.CategoryTree.setup({
				ctrlBoxSelector: '.editcontrols',
				treeSelector: '#editcategorytree',
				btnAddRootSelector: '#editcontrols-addroot',
				btnEditSelector: '#editcontrols-edit',
				btnDeleteSelector: '#editcontrols-delete',
				btnAddSelector: '#editcontrols-add'
			});
		});

	</script>

  <div data-role="header" data-position="fixed">
    <h1 uilang="editCategories"></h1>
    <a href="#navPanel" class="ui-btn"><i class="fa fa-bars"></i></a>
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
