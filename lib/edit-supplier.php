<?php
/* POST Script for editing suppliers */

include_once(dirname(__DIR__).'/classes/partdatabase.class.php');

$_POST += array('method' => 'none');

$su = $pdb->Suppliers();

// Response data structure
$response = array_replace_recursive($_GET, $_POST, array('success' => true));

// Input data structure
$data     = array_replace_recursive(array(
  'urlTemplate' => '',
  'name' => null,
  'id' => null,
  'method' => 'none'), $_GET, $_POST);

switch ($data['method']) {

  case 'edit': // Edit
    if( isset( $data['id'] ) && isset( $data['name'] ) && isset( $data['urlTemplate']) ) {

      $id          = $data['id'];
      $name        = $data['name'];
      $urlTemplate = $data['urlTemplate'];

      $supplier  = $su->GetById( $id );

      $isNameNew = $name != $supplier['name'];
      $isUrlNew  = $urlTemplate != $supplier['urlTemplate'];

      if( $isNameNew && $su->ExistsByName( $name ) ) {
        // Error, no duplicates allowed
        // Return error Message
        ob_clean();
        echo json_encode( array(
          'message' => "Lieferant existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      $response  = array_replace_recursive( $response, array(
        'name' => $supplier['name'],
        'urlTemplate' => $su->ExpandRawUrl( $supplier['urlTemplate'], "example" )
      ) );

      // Handle new image
      $imgFile = $data['imageFileName'];
      $setDefaultImg = $data['changeToDefaultImg'];

      if( isset( $setDefaultImg ) && $setDefaultImg == "true" ) {

        // Only delete...
        $pdb->Pictures()->DeleteAllByElementId($id, 'SU');
        $response += array(
          'newImageId' => null,
          'id' => $data['id'],
          'message' => 'Bild erfolgreich auf Standard zurückgesetzt.'
        );

      } else if( isset( $imgFile ) && $imgFile != "" ) {
        // Create database entry for image
        $pid = $pdb->Pictures()->Create($id, 'SU', $imgFile);

        if( $pid ) {
          $response += array(
            'message' => "Bild erfolgreich angepasst.",
            'success' => true,
            'newImageId' => $pid,
            'id' => $data['id']
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

      if( $isUrlNew ) {
        if( $su->SetUrlById( $id, $urlTemplate ) ) {
          $response = array_replace_recursive($response, array(
            'message' => "Url erfolgreich geändert.",
            'success' => true,
            'id' => $data['id'],
            'urlTemplate' => $su->ExpandRawUrl( $data['urlTemplate'], "example" )
          ));
        } else {

          // Clear buffer and print JSON
          ob_clean();

          // Return error Message
          echo json_encode( array(
            'message' => "Fehler beim Ändern der URL.",
            'success' => false,
            'type' => 'invalidId'
          ));
          return;
        }
      }

      if( $isNameNew ) {
        if( $su->SetNameById( $id, $name ) ) {

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
            'message' => "Fehler beim Ändern des Lieferantennamen.",
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
      if( $su->ExistsByName( $name ) ) {
        // Error, no duplicates allowed
        // Return error Message
        ob_clean();
        echo json_encode( array(
          'message' => "Lieferant existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      $pictureFileName = null;
      if( isset($data['imageFileName']) ) {
          $pictureFileName = $data['imageFileName'];
      }

      $newId = $su->Create( $name, $pictureFileName );
      if( $newId ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        $response = array_replace_recursive($response, array(
          'message' => "Lieferant erfolgreich hinzugefügt.",
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
          'message' => "Fehler beim Hinzufügen des Lieferants.",
          'success' => false,
          'type' => 'fail'
        ));
        return;
      }
    }

    break;

  case 'copy':
    if( isset( $data['name']) && isset( $data['id']) && $su->GetById($data['id']) ) {
      $name = $data['name'];
      $id   = $data['id'];
      // Check for duplicates
      if( $su->ExistsByName( $name ) ) {
        // Error, no duplicates allowed
        // Return error Message
        ob_clean();
        echo json_encode( array(
          'message' => "Lieferant existiert bereits.",
          'success' => false,
          'type' => 'notUnique'
        ));
        return;
      }

      // Check if a new or standard picture was selected
      if( isset($data['changeToDefaultImg']) && $data['changeToDefaultImg'] == 'true' ) {
        $newId = $su->Create( $name, "" );
      } elseif( isset($data['imageFileName']) && $data['imageFileName'] != "" ) {
        $newId = $su->Create( $name, $data['imageFileName'] );
      } else {
        $newId = $su->CreateFromId( $name, $id );
      }

      if( $newId ) {

        // Clear buffer and print JSON
        ob_clean();

        // Return error Message
        $response = array_replace_recursive($response, array(
          'message' => "Lieferant erfolgreich hinzugefügt.",
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
          'message' => "Fehler beim Hinzufügen des Lieferants.",
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
      if( !$su->DeleteById($id) ) {
        ob_clean();

        // Return error Message
        echo json_encode( array(
          'message' => "Fehler beim Löschen des Lieferanten.",
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
