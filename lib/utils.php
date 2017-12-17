<?php

/**
 * Translate jqGrid column name to MySQL table name
 * @param  string  $operator jqGrid operator string
 * @return function function of form fnc($column, $searchstring) that expands to the correct MySQL search string
 */
function TranslateJqGridColumnToMySQL($col) {
  switch( strtolower($col) ) {
    case "name":
      return "p.name";
    case "supplier":
      return "su.name";
    case "supplierid";
      return "p.id_supplier";
    case "storeloc":
      return "s.name";
    case "storelocid":
      return "s.id";
    case "comment":
      return "p.comment";
    case "instock":
      return "p.instock";
    case "mininstock":
      return "p.mininstock";
    case "footprintid":
    case "footprint":
      return "p.id_footprint";
    case "storelocid":
      return "p.id_storeloc";
    case "category_name":
      return "p.id_category";
    default:
      return "";
  }
}

/**
 * Translate jqGrid operators to MySQL
 * @param  string  $operator jqGrid operator string
 * @return function function of form fnc($column, $searchstring) that expands to the correct MySQL search string
 */
function TranslateJqGridToMySQL($operator) {
  switch( strtolower($operator) ) {
       case "eq":
         $operatorFn = function($col, $searchString) {
             return "STRCMP(LOWER($col),LOWER('$searchString')) <=> 0";
         };
         break;
       case "ne":
         $operatorFn = function($col, $searchString) {
             return "NOT (STRCMP(LOWER($col),LOWER('$searchString')) <=> 0)";
         };
         break;
       case "lt":
         $operatorFn = function($col, $searchString) {
             return "$col < '$searchString'";
         };
         break;
       case "le":
         $operatorFn = function($col, $searchString) {
             return "$col <= '$searchString'";
         };
         break;
       case "gt":
         $operatorFn = function($col, $searchString) {
             return "$col > '$searchString'";
         };
         break;
       case "ge":
         $operatorFn = function($col, $searchString) {
             return "$col >= '$searchString'";
         };
         break;
       case "bw":
         $operatorFn = function($col, $searchString) {
             return "$col LIKE '$searchString%'";
         };
         break;
       case "bn":
         $operatorFn = function($col, $searchString) {
             return "NOT $col LIKE '$searchString%'";
         };
         break;
       case "ew":
         $operatorFn = function($col, $searchString) {
             return "$col LIKE '%$searchString'";
         };
         break;
       case "en":
         $operatorFn = function($col, $searchString) {
             return "NOT $col LIKE '%$searchString'";
         };
         break;
       case "ni":
       case "nc":
         $operatorFn = function($col, $searchString) {
             return "NOT $col LIKE '%$searchString%'";
         };
         break;
       case "cn":
       case "in":
       default:
         $operatorFn = function($col, $searchString) {
             return "$col LIKE '%$searchString%'";
         };
     }
     return $operatorFn;
}

/**
 * Convert input data from a jqGrid Filter/Search to an object handeled by ShelfDB search functions
 * @param  array  $options jqGrid filter/search data
 * @return array  data that can be handeled by ShelfDB Part search functions
 */
function WrapJqGridFilterString($options) {

  $options["globalSearchString"] = trim($options["globalSearchString"]);

  $search = array("groupOp" => "AND", "rules" => array());

  // Global search string
  if( $options["_search"] != "true" || !empty($options["globalSearchString"] )) {
    $search["rules"][] = array(
      "name" => "any",
      "operator" => "cn",
      "data" => $options["globalSearchString"]
   );
  }

  // Filter
  if( $options["filters"] != "" ) {
    $options["filters"] = json_decode($options["filters"], true);
    $search["rules"][] = array(
      "groupOp" => $options["filters"]["groupOp"],
      "rules" => array_map(
        function($x) {
          return array(
            "name" => $x["field"],
            "operator" => $x["op"],
            "data" => $x["data"]
          );
        }, $options["filters"]["rules"]
      )
    );
  }
  if( $options["searchField"] != "" ) {
    $search["rules"][] = array(
        "name" => $options['searchField'],
        "operator" => $options['searchOper'],
        "data" => $options['searchString']
    );
  }

  return $search;
}

function endn($array)
{
  $array_local = $array;
  return end($array_local);
}

function sortn($array)
{
  $array_local = $array;
  return sort($array_local);
}

function set(&$setvar, &$getvar) {
  $setvar = isset($getvar) ? $getvar : $setvar;
}

/**
 * Safe variable read function with default return capability.
 * Retuns $default value if $var is not set.
 * @param  mixed $var     Variable to read
 * @param  mixed $default Default value, is null by default
 * @return mixed          Returns $default if $var is not set
 */
function get(&$var, $default=null) {
  return isset($var) ? $var : $default;
}

/**
 * Generate a version string from a major, minor associative ArrayAccess
 * @param  array $version version array
 * @return string          version string
 */
function getversionstring($version) : string {
  return $version["major"].".".$version["minor"];
}

function stempnam($folder, $prefix, $suffix ) {
  $files = glob(joinPaths(dirname(__DIR__),$folder,"*"));

  $createCandidate = function() use($folder,$prefix, $suffix) {
    return joinPaths(dirname(__DIR__),$folder, $prefix.uniqid().$suffix);
  };

  $candidate = $createCandidate();
  while( file_exists($candidate) ) {
    $candidate = $createCandidate();
  }

  return $candidate;

}

function joinPaths() {
    $args = func_get_args();
    $paths = array();

    foreach ($args as $arg) {
        $paths = array_merge($paths, (array)$arg);
    }

    if( sizeof($paths) > 0  ) {
      $frontSep = substr($paths[0],0,1) == '/' || substr($paths[0],0,1) == "\\" ;
    }

    $paths = array_map(function($p) {
      return trim($p, "\\/");
    }, $paths);
    $paths = array_filter($paths);
    return ($frontSep ? DIRECTORY_SEPARATOR : "" ).join(DIRECTORY_SEPARATOR, $paths);
}

function convertPathSepToForwardSlash($path) {
  return str_replace('\\', '/', $path);
}

function buildOptionHTMLFromArray($list, $valueKey, $nameKey) {

  foreach( $list as &$el ) {
    $elname = $el[$nameKey] ?? '';
    $elname = htmlspecialchars( $elname, ENT_QUOTES );
    $elname = preg_replace('/\G /', '&nbsp;', $elname);
    $el = '<option'.( isset($el[$valueKey]) ? ' value=\''.htmlspecialchars($el[$valueKey],ENT_QUOTES).'\'' : '' ). '>'.$elname.'</option>';
  }
  return '<select>\n'.join("\n",$list).'</select>\n';

}

// From https://stackoverflow.com/a/14972714/542269
function array_flatten($array) {

   $return = array();
   foreach ($array as $key => $value) {
       if (is_array($value)){ $return = array_merge($return, array_flatten($value));}
       else {$return[$key] = $value;}
   }
   return $return;

}

?>
