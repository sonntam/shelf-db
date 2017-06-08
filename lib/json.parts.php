<?php

  /**
   * Generate PartDB list of parts of a category and all its subcategories as JSON file
   */

  include_once(dirname(__DIR__).'/classes/partdatabase.class.php');

  $_GET += array("catid" => 0);
  $_GET += array("partid" => null);
  $_GET += array("rows" => 50);
  $_GET += array("page" => 1);
  $_GET += array("sidx" => "name");      // name of sorting column index
  $_GET += array("sord" => "asc");       // "asc" or "desc"
  $_GET += array("_search" => false);    // If true this is a search request
  $_GET += array("searchField" => null); // Name of search field
  $_GET += array("searchString" => null);// Value of search field
  $_GET += array("searchOper" => null);  // Search operator "cn" = contains; "nc" = contains not; "eq" = equals; "ne" = is not; "bw" = begins with; "bn" = begins not with; "ew" = ends with; "en" = ends not with
  $_GET += array("filters" => null);     // Filters


  // Get category ID
  $catid      = $_GET["catid"];
  $partid     = $_GET["partid"];

  if( $partid === null ) { // Get list of all parts of category

    $limit      = $_GET["rows"];
    $page       = $_GET["page"];

    // http://www.trirand.com/blog/jqgrid/jqgrid.html
    $numparts = $pdb->GetNumberOfPartsByCategoryId($catid, true);
    $numpages = ceil($numparts/$limit);
    $page     = min($numpages,$page);

    $offset   = $limit*($page - 1);

    $parts = $pdb->GetPartsSegmentByCategoryId($catid, $offset, $limit, $_GET["sidx"], $_GET["sord"], true);

    // Copy
    $newparts = array();
    for( $i = 0; $i < count($parts); $i++)
    {
      $newparts[$i]['cell'] = $parts[$i];
      $newparts[$i]['id'] = $parts[$i]['id'];
      $newparts[$i]['name'] = $parts[$i]['name'];
    }

    $responce->page    = $page;
    $responce->total   = $numpages;
    $responce->records = $numparts;

    $responce->rows    = $newparts;

    $json = json_encode($responce, JSON_PRETTY_PRINT);

    // Clear buffer and print JSON
    ob_clean();

    echo $json;

  } else {  // Single part information
    $parts = array( $pdb->GetPartById($partid) );

    $json = json_encode($parts, JSON_PRETTY_PRINT);

    // Clear buffer and print JSON
    ob_clean();

    echo $json;
  }


?>
