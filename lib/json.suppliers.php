<?php

  include_once(dirname(__DIR__).'/classes/shelfdb.class.php');

  $data = array_replace_recursive(
    array(
      "id" => null,
      "partNr" => null,
      "partId" => null
    ), $_GET, $_POST );

  $response = array(
    "success" => true
  );

  $su = $pdb->Suppliers()->GetById($data['id']);

  if( $su ) {

    if( !$data['id'] ) { // Get all Suppliers

      if( $data['partNr'] ) {
        foreach( $su as &$s ) {
          $s['urlTemplate'] = $pdb->Suppliers()->ExpandRawUrl( $s['urlTemplate'], $data['partNr'] );
        }
      }

    } else {
      // Single supplier
      if( $data['partId'] ) {
        $part = $pdb->Parts()->GetById($data['partId']);
        if( !$part ) {
          $response = array_replace_recursive($response,
            array(
              "message" => "partId does not exist.",
              "success" => false
            ));
        } else {
          $su['urlTemplate'] = $pdb->Suppliers()->ExpandRawUrl( $su['urlTemplate'], $part['supplierpartnr'] );
        }
      } elseif( $data['partNr'] ) {
        $su['urlTemplate'] = $pdb->Suppliers()->ExpandRawUrl( $su['urlTemplate'], $data['partNr'] );
      }
    }
  } else {
    // $su invalid
    $response = array_replace_recursive($response,
      array(
        "message" => "Error retrieving supplier.",
        "success" => false
      ));
  }
  $response = array_replace_recursive($response,$su);

  $json = json_encode($response, JSON_PRETTY_PRINT);

  // Clear buffer and print JSON
  ob_clean();

  echo $json;
?>
