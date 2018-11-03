<?php

  include_once(dirname(__DIR__).'/classes/ShelfDB.class.php');
  include_once(__DIR__.'/utils.php');

  \Log::SetErrorOuputJSON();

  $data = array();

  // Handle passed data
  $args = array_replace_recursive(array('type' => '', 'tempFilename' => NULL),$_GET,$_POST);

  $sourceFile = $args['tempFilename'];

  switch($args['type']) {
    case 'moveTempToTarget':
      $sourceFile = joinPaths('/attachments/tmp', basename($sourceFile) );
      break;
    case 'uploadToTemp':
      // Delete old temporary files in folder
      $pdb->DeleteOldTempFiles();
      break;
    default: return;
  }

  switch($args['target']) {
    case 'footprintImage':
      $uploadDir = '/img/footprint';
      break;
    case 'supplierImage':
      $uploadDir = '/img/supplier';
      break;
    case 'partImage':
      $uploadDir = '/img/parts';
      break;
    case 'tempImage':
    case 'tempFile':
      $uploadDir = '/attachments/tmp';
      break;
    case 'datasheetFile':
      $uploadDir = '/attachments/datasheets';
      break;
    default:
      return;
  }

  $uploadFullDir = joinPaths($pdb->AbsRoot(),$uploadDir);
  $uploadRelDir  = joinPaths($pdb->RelRoot(),$uploadDir);

  if( sizeof($_FILES) > 0) {  // Upload
    $error = false;
    $files = array();

    foreach($_FILES as $file)
    {
        $pathParts  = pathinfo($file['name']);
        $pathParts['extension'] = ( $pathParts['extension'] != "" ? ".".$pathParts['extension'] : "");

        $uniqueFile = stempnam( $uploadDir, "", $pathParts['extension'] );

        $uniquePathParts  = pathinfo($uniqueFile);

        try {
          if( move_uploaded_file($file['tmp_name'], $uniqueFile ) )
          {
              $files[] = array('fullpath' => convertPathSepToForwardSlash(joinPaths($uploadRelDir,$uniquePathParts['basename'])),
                               'name' => $uniquePathParts['basename'] );
          }
          else
          {
              $error = true;
          }
        } catch( Throwable $t) {
          $error = true;
        }
    }
    $data = ($error) ? array('error' => 'There was an error uploading your files') : array('files' => $files);
  }
  elseif( isset($sourceFile) )  // Move
  {
      // Copy file
      $error = false;
      $sourcePathParts = pathinfo($sourceFile);

      $targetFileExists = file_exists(joinPaths($uploadFullDir, $sourcePathParts['basename']));

      if( $targetFileExists
        || rename(joinPaths(dirname(__DIR__),$sourceFile), joinPaths($uploadFullDir, $sourcePathParts['basename']) ) ) {
        $files[] = array(
          'fullpath' => convertPathSepToForwardSlash(joinPaths($uploadRelDir, $sourcePathParts['basename'])),
          'name'     => $sourcePathParts['basename'] );
        } else {
          $error = true;
        }

      $data = ($error) ? array('error' => 'There was an error moving the file') : array('files' => $files);
  }

  ob_clean();

  echo json_encode($data);

?>
