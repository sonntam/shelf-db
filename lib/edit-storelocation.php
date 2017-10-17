<?php
/* POST Script for editing footprints */

include_once(dirname(__DIR__).'/classes/shelfdb.class.php');

$_POST += array('method' => 'none');

$fp = $pdb->StoreLocation();

$response = array_replace_recursive($_GET, $_POST, array('success' => true));

switch ($_POST['method']) {

  case 'edit': // Edit
    if( isset( $_POST['id'] ) && isset( $_POST['name'] ) ) {

      $id   = $_POST['id'];
      $name = $_POST['name'];

      $isNameNew = $name != $fp->GetNameById( $id );

      if( $isNameNew && $fp->ExistsByName( $name ) ) {
        // Error, no duplicates allowed
        // Return error Message
        ob_clean();
        echo json_encode( array(
          'message' => "Lagerort existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      if( $isNameNew ) {
        if( $fp->SetNameById( $id, $name ) ) {

          // Clear buffer and print JSON
          $response = array_replace_recursive($response, array(
            'message' => "Name erfolgreich geändert.",
            'success' => true,
            'id' => $_POST['id'],
            'name' => $_POST['name']
          ));
        } else {

          // Clear buffer and print JSON
          ob_clean();

          // Return error Message
          echo json_encode( array(
            'message' => "Fehler beim Ändern des Lagerortenamens.",
            'success' => false,
            'type' => 'invalidId'
          ));
          return;
        }
      }
    }
    break;

  /**
   * Add new category
   *
   */
  case 'add':
    if( isset( $_POST['name'] ) ) {
      $name = $_POST['name'];
      // Check for duplicates
      if( $fp->ExistsByName( $name ) ) {
        // Error, no duplicates allowed
        // Return error Message
        ob_clean();
        echo json_encode( array(
          'message' => "Lagerort existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      $newId = $fp->Create( $name );
      if( $newId ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        $response = array_replace_recursive($response, array(
          'message' => "Lagerort erfolgreich hinzugefügt.",
          'success' => true,
          'name' => $name,
          'id' => $newId['id']
        ));

      } else {
        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Fehler beim Hinzufügen der Kategorien.",
          'success' => false,
          'type' => 'fail'
        ));
        return;
      }
    }

    break;

  case 'delete':
    if( isset( $_POST['id'] ) ) {
      $id = $_POST['id'];
      if( !$fp->DeleteById($id) ) {
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Fehler beim Löschen der Lagerort.",
          'success' => false,
          'id' => $id
        ));

        return;
      }
    }
    break;

  default:

    break;
}

// Clear buffer and print JSON
ob_clean();

// Return error Message
echo json_encode($response);

?>
