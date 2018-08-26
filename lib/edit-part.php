<?php
  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  /*
  $_REQUESTarray[7]
$_REQUEST['name']"Dyneema Abspannleine 5m, schwarz, 2mm (TRX)"
$_REQUEST['partnum']"1/0"
$_REQUEST['footprint']"66"
$_REQUEST['storeloc']"81"
$_REQUEST['datasheet']""
$_REQUEST['oper']"edit"
$_REQUEST['id']"763"
   */
  $p = $pdb->Part();

  // Submitted data
  $data = array_replace_recursive(array(
    'method' => 'none',
    'field' => '',
    'data' => null,
    'id' => null
  ), $_GET, $_POST );

  function jqGridTranslate($data) {
    $jqTranslate = array( 'oper' => array('_newkey' => 'method', 'del' => 'delete') );

    foreach($jqTranslate as $key => $value) {
      if( isset($data[$key]) ) {
        if( array_key_exists( $data[$key], $value ) )
          $data[$key] = $value[$data[$key]];

        // Rename
        $data[$value['_newkey']] = $data[$key];
        unset($data[$key]);
      }
    }
    return $data;
  }

  // Translate from jqgrid
  $data = jqGridTranslate($data);

  $response = array(
    'success' => false,
    'method' => $data['method'],
    'id' => $data['id']
  );

  // Actions
  switch($data['method']) {
    case 'add':

      $newItem = $p->Create($data['name'], $data['category_name'],
        $data['footprint'], $data['storeloc'], 0,
        $data['mininstock'], $data['totalstock'], $data['instock'] );

      if( $newItem ) {
        $response = array_replace_recursive($response, array(
          'success' => true,
          'newid' => $newItem['id']
        ));
      }
      break;

    case 'delete':
      if( isset($data['id']) ) {
        $id = $data['id'];
        if( $p->DeleteById($id) ) {
          $response = array_replace_recursive($response, array(
            'success' => true
          ));
        }
      }
      break;

    case 'addPicture':
      if( isset($data['imageFileName']) && isset($data['id']) ) {
        $id = $data['id'];
        $newid = $pdb->Picture()->Create($id, 'P', $data['imageFileName'], false);

        if( $newid ) {
          $response = array_replace_recursive($response, array(
            'success' => true,
            'pictureId' => $newid,
            'imageFileName' => $data['imageFileName'],
            'imageFullPath' => $pdb->RelRoot() . 'img/parts/' . $data['imageFileName']
          ));
        }
      }
      break;

    case 'deletePicture':
      if( isset($data['pictureId']) ) {
        $res = $pdb->Picture()->DeleteById($data['pictureId']);

        if( $res ) {
          $response = array_replace_recursive($response, array(
            'success' => true,
            'pictureId' => $data['pictureId']
          ));
        }
      }
      break;

    case 'edit': // Edit command from table
      if( isset($data['id']) ) {
        $id = $data['id'];
        $response['success'] = true;

        if( isset($data['name'] ))
          $response['success'] = $response['success'] && $p->SetNameById($id, $data['name']);

        if( isset($data['mininstock'] ))
          $response['success'] = $response['success'] && $p->SetMinInStockById($id, $data['mininstock']);

        if( isset($data['footprint'] ))
          $response['success'] = $response['success'] && $p->SetFootprintById($id, $data['footprint']);

        if( isset($data['storeloc'] ))
          $response['success'] = $response['success'] && $p->SetStorageLocationById($id, $data['storeloc']);

        if( isset($data['category_name'] ))
          $response['success'] = $response['success'] && $p->SetCategoryById($id, $data['category_name']);
      }

      break;

    case 'setMasterPic':
      $picId = $data['id'];

      $res = $pdb->Picture()->SetPartMasterById($picId);

      $response = array_replace_recursive($response, array(
        'success' => ($res ? true : false)
      ));

      break;

    case 'editDetail':

      if( $data['id'] ) {
        $id = $data['id'];

        // Call table
        $methodTable = array(
          'supplierpartnr' => 'SetPartNumberById',
          'supplierid' => 'SetSupplierById',
          'name' => 'SetNameById',
          'category' => 'SetCategoryById',
          'footprint' => 'SetFootprintById',
          'storelocation' => 'SetStorageLocationById',
          'comment' => 'SetCommentById',
          'price' => 'SetPriceById',
          'totalstock' => 'SetTotalStockById',
          'instock' => 'SetInStockById',
          'mininstock' => 'SetMinInStockById'
        );

        $field = strtolower($data['field']);

        // Check if method exists then call and return result
        if( array_key_exists( $field, $methodTable )
          && is_callable( array($p, $methodTable[$field] ) ) ) {
          $method = $methodTable[$field];
          $response['success'] = $p->$method( $id, $data['data'] );
          if( $field == 'price' ) // Format price and send back
            $response['pricetext'] = $p->FormatPrice($data['data']);
        }
      }
      break;

    default:
      break;
  }

  // Send response
  ob_clean();

  echo json_encode($response) ;

?>
