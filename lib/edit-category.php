<?php
/* POST Script for editing categories */

include_once(dirname(__DIR__).'/classes/shelfdb.class.php');

$data = array_replace_recursive(
  array(
    'method' => 'none',
    'id' => null
  ), $_POST);

$response = array(
  'success' => true
);

$cat = $pdb->Categories();

switch ($data['method']) {

  case 'editName': // Edit name
    if( $data['id'] && isset( $data['newname'] ) ) {
      if( $cat->SetNameById( $data['id'], $data['newname'] ) ) {

        $response = array_replace_recursive( $response, array(
          'message' => "Name erfolgreich geändert.",
          'success' => true,
          'id' => $data['id'],
          'newname' => $data['newname']
        ));
      } else {
        $response = array_replace_recursive( $response, array(
          'message' => "Fehler beim Ändern des Kategorienamens.",
          'success' => false
        ));
      }
    }
    break;

  case 'add': // Add new category
    if( isset( $data['parentid'] ) && isset( $data['newname'] ) ) {
      if( $newid = $cat->Create( $data['parentid'], $data['newname'] ) ) {

        $response = array_replace_recursive( $response, array(
          'message' => "Kategorie erfolgreich hinzugefügt.",
          'success' => true,
          'parentid' => $data['parentid'],
          'newname' => $data['newname'],
          'newid' => $newid
        ));

      } else {
        $response = array_replace_recursive( $response, array(
          'message' => "Fehler beim Hinzufügen der Kategorien.",
          'success' => false
        ));
      }
    }

    break;

  case 'delete':

    if( $data['id'] ) {
      // Now check if this is not a non-empty category with leaf categories
      $id = $data['id'];

      // Get part count
      $numParts = $pdb->Parts()->GetCountByCategoryId( $id, "", true );

      // Get Sub-categories
      $subCatIds = $cat->GetSubcategoryIdsFromId( $id, false );
      if( $subCatIds && sizeof($subCatIds) > 0 && $numParts > 0 ) {
        // Disallow!
        $response = array_replace_recursive( $response, array(
          'message' => "Nur leere Zwischenkategorien können gelöscht werden.",
          'success' => false
        ));
      } else {
        // Move all items within this category to the parent category
        $response = array_replace_recursive( $response, array(
          'success' => false
        ));
        $parentCatId = $cat->GetParentFromId($id);
        if( $parentCatId ) {
          if( $pdb->Parts()->AllReplaceStorelocationId($id,$parentCatId['id']) ) {
            // Delete category
            if( $cat->DeleteById($id) ) {
              $response = array_replace_recursive( $response, array(
                'success' => true,
                'message' => 'Kategorie erfolgreich gelöscht und enthaltene Teile in die übergeordnete Kategorie verschoben.',
                'id' => $id,
                'newParentId' => $parentCatId['id']
              ));
            } else {
              $response = array_replace_recursive( $response, array(
                'message' => 'Enthaltene Teile in die übergeordnete Kategorie verschoben, aber Kategorie konnte nicht gelöscht werden.'
              ));
            }
          } else {
            $response = array_replace_recursive( $response, array(
              'message' => 'Teile konnten nicht in die übergeordnete Kategorie verschoben werden.'
            ));
          }
        }
      }

    }

    break;

  case 'movecat':

    if( isset( $data['newparentid'] ) && isset( $data['id'] ) ) {
      if( $cat->MoveToParentById( $data['id'], $data['newparentid'] ) ) {

        $response = array_replace_recursive( $response, array(
          'message' => "Kategorie erfolgreich verschoben.",
          'success' => true,
          'parentid' => $data['newparentid'],
          'id' => $data['id']
        ));

      } else {
        $response = array_replace_recursive( $response, array(
          'message' => "Fehler beim Verschieben der Kategorien.",
          'success' => false
        ));
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
