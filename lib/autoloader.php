<?php

  require_once(__DIR__.'/utils.php');
  require_once(dirname(__DIR__).'/classes/log.php');
  require_once(__DIR__.'/Twig/Autoloader.php');

  spl_autoload_extensions( '.php,.class.php' );
  Twig_Autoloader::register();
  spl_autoload_register( function( $class_name ) {

    $loadFcn = function( $loadPath, $className ) {

        $extArr = explode(",",spl_autoload_extensions());

        foreach( $extArr as $ext) {
          $candidate = $loadPath.$ext;
          if( file_exists($candidate) ) {
            require_once($candidate);
            return true;
          }
        }

        Log::Error("Could not find include for $loadPath");
        return false;
    };

    $class_path = str_replace("\\",DIRECTORY_SEPARATOR,$class_name);

    // Generate list of files to try
    $tries = array(
      joinPaths(__DIR__, $class_path),
      joinPaths(dirname(__DIR__),'classes',$class_path),
    );

    foreach( $tries as $try ) {
      if( $loadFcn($try, $class_name) ) {
        return true;
      }
    }
    Log::LogPhpException( new Exception("Could not load required include file for class $class_name.") );

    return false;
  });
?>
