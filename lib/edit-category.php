<?php
/* POST Script for editing categories */

include_once(dirname(__DIR__).'/classes/partdatabase.class.php');

$_POST += array('method' => 'none');

switch ($_POST['method']) {

  case 'editcatname': // Edit name
    if( isset( $_POST['id'] ) && isset( $_POST['newname'] ) ) {
      if( $pdb->SetCategoryNameById( $_POST['id'], $_POST['newname'] ) ) {

        // Clear buffer and print JSON
        ob_clean();

        echo json_encode( array(
          'message' => "Name erfolgreich geändert.",
          'success' => true,
          'id' => $_POST['id'],
          'newname' => $_POST['newname']
        ));
      } else {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Fehler beim Ändern des Kategorienamens.",
          'success' => false
        ));
      }
    }
    break;

  case 'addcat': // Add new category
    if( isset( $_POST['parentid'] ) && isset( $_POST['newname'] ) ) {
      if( $newid = $pdb->AddCategoryToParentById( $_POST['parentid'], $_POST['newname'] ) ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Kategorie erfolgreich hinzugefügt.",
          'success' => true,
          'parentid' => $_POST['parentid'],
          'newname' => $_POST['newname'],
          'newid' => $newid
        ));

      } else {
        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Fehler beim Hinzufügen der Kategorien.",
          'success' => false
        ));
      }
    }

    break;

  case 'deletecat':

    break;

  case 'movecat':

    if( isset( $_POST['newparentid'] ) && isset( $_POST['id'] ) ) {
      if( $pdb->MoveCategoryToParentById( $_POST['id'], $_POST['newparentid'] ) ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Kategorie erfolgreich verschoben.",
          'success' => true,
          'parentid' => $_POST['newparentid'],
          'id' => $_POST['id']
        ));

      } else {
        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Fehler beim Verschieben der Kategorien.",
          'success' => false
        ));
      }
    }

    break;

  default:

    break;
}

?>
