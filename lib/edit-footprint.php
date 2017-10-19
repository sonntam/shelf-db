<?php
/* POST Script for editing footprints */

include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

$_POST += array('method' => 'none');

$fp = $pdb->Footprint();

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
          'message' => "Bauform existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      // Handle new image
      $imgFile = $_POST['imageFileName'];
      $setDefaultImg = $_POST['changeToDefaultImg'];

      if( isset( $setDefaultImg ) && $setDefaultImg == "true" ) {

        // Only delete...
        $pdb->Picture()->DeleteAllByElementId($id, 'F');
        $response += array(
          'newImageId' => null,
          'id' => $_POST['id'],
          'message' => 'Bild erfolgreich auf Standard zurückgesetzt.'
        );

      } else if( isset( $imgFile ) && $imgFile != "" ) {
        // Create database entry for image
        $pid = $pdb->Picture()->Create($id, 'F', $imgFile);

        if( $pid ) {
          $response += array(
            'message' => "Bild erfolgreich angepasst.",
            'success' => true,
            'newImageId' => $pid,
            'id' => $_POST['id']
          );
        } else {
          // Clear buffer and print JSON
          ob_clean();

          // Return error Message
          echo json_encode( array(
            'message' => "Fehler beim Eintragen des Bilds.",
            'success' => false,
            'type' => 'pictureError'
          ));
          return;
        }
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
            'message' => "Fehler beim Ändern des Kategorienamens.",
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
          'message' => "Bauform existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      $pictureFileName = null;
      if( isset($_POST['imageFileName']) ) {
          $pictureFileName = $_POST['imageFileName'];
      }

      $newId = $fp->Create( $name, $pictureFileName );
      if( $newId ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        $response = array_replace_recursive($response, array(
          'message' => "Bauform erfolgreich hinzugefügt.",
          'success' => true,
          'name' => $name,
          'id' => $newId['id'],
          'picId' => $newId['picid']
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

  case 'copy':
    if( isset( $_POST['name']) && isset( $_POST['id']) && $fp->GetById($_POST['id']) ) {
      $name = $_POST['name'];
      $id   = $_POST['id'];
      // Check for duplicates
      if( $fp->ExistsByName( $name ) ) {
        // Error, no duplicates allowed
        // Return error Message
        ob_clean();
        echo json_encode( array(
          'message' => "Bauform existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      // Check if a new or standard picture was selected
      if( isset($_POST['changeToDefaultImg']) && $_POST['changeToDefaultImg'] == 'true' )
      {
        $newId = $fp->Create( $name, "" );
      } elseif( isset($_POST['imageFileName']) && $_POST['imageFileName'] != "" ) {
        $newId = $fp->Create( $name, $_POST['imageFileName'] );
      } else {
        $newId = $fp->CreateFromId( $name, $id );
      }

      if( $newId ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        $response = array_replace_recursive($response, array(
          'message' => "Bauform erfolgreich hinzugefügt.",
          'success' => true,
          'name' => $name,
          'id' => $newId['id'],
          'picId' => $newId['picid']
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
          'message' => "Fehler beim Löschen der Bauform.",
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
