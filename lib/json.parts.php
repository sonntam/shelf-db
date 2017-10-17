<?php

  /**
   * Generate ShelfDB list of parts of a category and all its subcategories as JSON file
   */

  include_once(dirname(__DIR__).'/classes/shelfdb.class.php');
  include_once(dirname(__DIR__).'/lib/utils.php');

  $defaults = array(
    "catid"              => 0,
    "partid"             => null,
    "rows"               => 50,
    "page"               => 1,
    "sidx"               => "name",    // name of sorting column index
    "sord"               => "asc",     // "asc" or "desc"
    "_search"            => false,     // If true this is a search request
    "searchField"        => null,      // Name of search field
    "searchString"       => null,      // Value of search field
    "searchOper"         => null,      // Search operator "cn" = contains; "nc" = contains not; "eq" = equals; "ne" = is not; "bw" = begins with; "bn" = begins not with; "ew" = ends with; "en" = ends not with
    "filters"            => null,      // Filters
    "globalSearchString" => "",
    "getDetailed"        => false,
    "getSubcategories"   => true
  );

  $options = array_replace_recursive( $defaults, $_GET, $_POST );

  // Get category ID
  $catid      = $options["catid"];
  $partid     = $options["partid"];

  if( $partid === null ) { // Get list of all parts of category

    $limit      = $options["rows"];
    $page       = $options["page"];
    $recursive  = filter_var($options["getSubcategories"], FILTER_VALIDATE_BOOLEAN);

    // http://www.trirand.com/blog/jqgrid/jqgrid.html

    // Check if this is a filtered search or a global search
    $search   = WrapJqGridFilterString($options);
    $numparts = $pdb->Part()->GetCountByCategoryId($catid, $search, $recursive);
    $numpages = ceil($numparts/$limit);
    $page     = min($numpages,$page);

    $offset   = $limit*($page - 1);
    $parts    = $pdb->Part()->GetSegmentByCategoryId($catid, $offset, $limit, $options["sidx"], $options["sord"], $recursive, $search);

    // Copy
    $newparts = array();
    for( $i = 0; $i < count($parts); $i++)
    {
      $newparts[$i]['cell'] = $parts[$i];
      $newparts[$i]['id'] = $parts[$i]['id'];
      $newparts[$i]['name'] = $parts[$i]['name'];
    }

    $response = new stdClass();
    $response->page    = $page;
    $response->total   = $numpages;
    $response->records = $numparts;

    $response->rows    = $newparts;

    $json = json_encode($response, JSON_PRETTY_PRINT);

    // Clear buffer and print JSON
    ob_clean();

    echo $json;

  } else {  // Single part information
    if( $options["getDetailed"] )
      $parts = array( $pdb->Part()->GetDetailsById($partid) );
    else
      $parts = array( $pdb->Part()->GetById($partid) );

    $json = json_encode($parts[0], JSON_PRETTY_PRINT);

    // Clear buffer and print JSON
    ob_clean();

    echo $json;
  }

?>
