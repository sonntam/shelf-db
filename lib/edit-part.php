<?php
  include_once(dirname(__DIR__).'/classes/shelfdb.class.php');

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
  $p = $pdb->Parts();

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
    'success' => false
  );

  // Actions
  switch($data['method']) {
    case 'add':
      # code...
      break;
    case 'delete':
      if( isset($data['id']) ) {
        $id = $data['id'];
        if( $p->DeleteById($id) ) {
          $response = array_replace_recursive($response, array(
            'success' => true,
            'id' => $id,
            'method' => 'delete'
          ));
        }
      }
      break;
    case 'edit': // Edit command from table

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
          'price' => 'SetPriceById'
        );

        $field = strtolower($data['field']);

        // Check if method exists then call and return result
        if( array_key_exists( $field, $methodTable )
          && is_callable( array($p, $methodTable[$field] ) ) ) {
          $method = $methodTable[$field];
          $response['success'] = $p->$method( $id, $data['data'] );
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
