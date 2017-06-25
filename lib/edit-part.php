<?php

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

  // Submitted data
  $data = array_replace_recursive(array(
    'method' => 'none'
  ), $_GET, $_POST );

  $response = array(
    'success' => false
  );

  // Actions
  switch($data['method']) {
    case 'add':
      # code...
      break;
    case 'delete':
      # code...
      break;
    case 'edit':
      # code...
      break;

    default:
      break;
  }

  // Send response
  ob_clean();

  echo json_encode($resonse) ;

?>
