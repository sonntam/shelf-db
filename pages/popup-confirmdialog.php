<?php
  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $data = array_replace_recursive(
    array(
      'okButtonType_bs' => 'primary', // 'danger'
      'header' => '',
      'text' => '',
      'confirmButtonText' => null
    ), $_GET, $_POST );

    echo $pdb->RenderTemplate('popup-confirmdialog.twig', array(
      "okButtonType_bs" => $data['okButtonType_bs'],
      "langLabels" => array(
        "dialogHeader" => $data["header"],
        "dialogText" => $data["text"],
        "confirmButtonText" => $data["confirmButtonText"]
       )
    ));

    return;
?>
