<?php
/* POST Script for editing footprints */

include_once(dirname(__DIR__).'/classes/partdatabase.class.php');

$fp = $pdb->StoreLocations();

$data = array_replace_recursive( array('method' => 'none'), $_GET, $_POST );
$response = array_replace_recursive($data, array('success' => false));

switch ($data['method']) {

  case 'logout':
    $pdb->Users()->LogOut();
    $response = array_replace_recursive($response, array(
      'success' => true
    ));
    break;

  case 'authenticate': // Auth

    if( isset($data['username']) && isset($data['password']) ) {
      if( $pdb->Users()->LoginUser($data['username'], $data['password']) ) {
        $response = array_replace_recursive($response, array(
          'success' => true
        ));
      }
    }

    break;

  case 'edit': // Edit
    if( isset( $data['id'] ) && isset( $data['name'] ) ) {

      $id   = $data['id'];
      $name = $data['name'];

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
            'id' => $data['id'],
            'name' => $data['name']
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
    if( isset( $data['name'] ) ) {
      $name = $data['name'];
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
    if( isset( $data['id'] ) ) {
      $id = $data['id'];
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
