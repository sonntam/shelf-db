<?php

namespace ShelfDB {

  class Parts {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    public function AllReplaceFootprintId( int $oldid, int $newid ) {
      $query = "UPDATE parts SET id_footprint = $newid WHERE id_footprint = $oldid;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }

    public function AllReplaceSupplierId( int $oldid, int $newid ) {
      $query = "UPDATE parts SET id_supplier = $newid WHERE id_supplier = $oldid;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }

    public function AllReplaceStorelocationId( int $oldid, int $newid ) {
      $query = "UPDATE parts SET id_storeloc = $newid WHERE id_storeloc = $oldid;";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      return $res;
    }

    private function GetCountByCategoryIdEscaped( int $catid = 0, $escapedSearch = "", $recursive = false ) {

      $partcount = 0;
      $clauses = array();

      if( $recursive && $catid == 0 ) {
        $recursive = false;
      } else {
        $clauses[] = "p.id_category = $catid";
      }

      $clauses[] = "("
        ."f.name LIKE '%$escapedSearch%' OR "
        ."s.name LIKE '%$escapedSearch%' OR "
        ."su.name LIKE '%$escapedSearch%' OR "
        ."c.name LIKE '%$escapedSearch%' OR "
        ."p.name LIKE '%$escapedSearch%' OR "
        ."p.comment LIKE '%$escapedSearch%')";

      $query = "SELECT COUNT(p.id) as partcount FROM parts p "
          ."LEFT JOIN footprints f ON p.id_footprint = f.id "
          ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
          ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
          ."LEFT JOIN categories c ON p.id_category = c.id "
        ."WHERE ". join(' AND ', $clauses) .";";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $data = $res->fetch_assoc();
      $res->free();

      $partcount = $data['partcount'];

      // recurse
      if($recursive)
      {
        // Get child categories
        $query = "SELECT id FROM categories WHERE parentnode = $catid";
        $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
        $children = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        foreach( $children as $category )
        {
          $partcount += $this->GetCountByCategoryIdEscaped( $category['id'], $search, true );
        }
      }

      return $partcount;

    }

    public function GetCountByCategoryId( int $catid = 0, $search = "", $recursive = false ) {

      $partcount = 0;

      if( $search && $search != "" ) {
        $search = $this->db->sql->real_escape_string($search);
        return $this->GetCountByCategoryIdEscaped( $catid, $search, $recursive);
      }

      $query = "SELECT COUNT(p.id) as partcount FROM parts p WHERE p.id_category = $catid";

      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      $data = $res->fetch_assoc();
      $res->free();

      $partcount += $data['partcount'];

      // recurse
      if($recursive)
      {
        // Get child categories
        $query = "SELECT id FROM categories WHERE parentnode = $catid";
        $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
        $children = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        foreach( $children as $category )
        {
          $partcount += $this->GetCountByCategoryId( $category['id'], $search, true );
        }
      }

      return $partcount;
    }

    private function GetListByCategoryId($search, int $catid, int $offset, int $limit, $sortcol, $sortorder, $recursive = false) {
      if( $recursive ) {
        $catids = $this->db->Categories()->GetSubcategoryIdsFromId( $catid, true );
      } else {
        $catids = array($catid);
      }

      if( !$search || $search == "" ) {
        $searchFilter = "";
      } else {
        $search = $this->db->sql->real_escape_string($search);
        $searchFilter = "AND ("
          ."f.name LIKE '%$search%' OR "
          ."s.name LIKE '%$search%' OR "
          ."su.name LIKE '%$search%' OR "
          ."c.name LIKE '%$search%' OR "
          ."p.name LIKE '%$search%' OR "
          ."p.comment LIKE '%$search%') ";
      }

      switch( $sortcol )
      {
        case "instock":
          $sortname = "p.instock";
          break;
        case "mininstock":
          $sortname = "p.mininstock";
          break;
        case "footprint":
          $sortname = "f.name";
          break;
        case "storelocid":
          $sortname = "s.name";
          break;
        default:
          $sortname = "p.name";
      }

      $sortorder = ($sortorder == "desc" ? "DESC" : "ASC");

      $query = "SELECT p.*, p.id, p.id_category, p.name AS name, p.instock AS instock, p.mininstock AS mininstock, "
        ."CONCAT(p.instock,'/',p.mininstock) AS partnum, COALESCE(f.name,'-') AS footprint, "
        ."s.id AS storelocid, s.name AS storeloc, "
        ."su.name AS supplier_name, c.name AS category_name, "
        ."pic.pict_id_arr, pic.pict_fname_arr, pic.pict_height_arr, pic.pict_width_arr, "
        ."pic.pict_masterpict_arr, pic.tn_id_arr, pic.tn_fname_arr, pic.tn_obsolete_arr, "
        ."pic.tn_t_arr,"
        ."pr.price AS price, "
        ."COALESCE(fpr.pict_fname,'default.png') AS f_pict_fname, "
        ."fpr.pict_width as f_pict_width, "
        ."fpr.pict_height as f_pict_height "
        ."FROM parts p "
        ."LEFT JOIN footprints f ON p.id_footprint = f.id "
        ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
        ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
        ."LEFT JOIN categories c ON p.id_category = c.id "
        ."LEFT JOIN ("
          ."SELECT GROUP_CONCAT(a.id) AS pict_id_arr, a.parent_id, GROUP_CONCAT(a.pict_fname SEPARATOR '/') AS pict_fname_arr, GROUP_CONCAT(a.pict_height) AS pict_height_arr, "
            ."GROUP_CONCAT(a.pict_width) AS pict_width_arr, GROUP_CONCAT(a.pict_masterpict) AS pict_masterpict_arr, GROUP_CONCAT(b.id) AS tn_id_arr, GROUP_CONCAT(b.pict_fname SEPARATOR '/') AS tn_fname_arr, "
            ."GROUP_CONCAT(b.tn_obsolete) AS tn_obsolete_arr, GROUP_CONCAT(b.tn_t) AS tn_t_arr FROM pictures a LEFT JOIN pictures b ON b.tn_pictid = a.id "
          ."WHERE a.pict_type = 'P' GROUP BY parent_id"
        .") pic ON pic.parent_id = p.id " //AND (pic.pict_masterpict = 1 OR pic.pict_) "
        ."LEFT JOIN prices pr ON pr.part_id = p.id "
        ."LEFT JOIN ("
          ."SELECT * FROM pictures WHERE pict_type = 'F'"
        .") fpr ON f.id = fpr.parent_id "
        ."WHERE p.id_category IN (".join(',', $catids ).") "
          .$searchFilter
        ."ORDER BY udf_NaturalSortFormat($sortname, 10, \".,\") $sortorder LIMIT $limit OFFSET $offset";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
      if( !$res )
      {
        return null;
      }
      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      foreach( $data as &$part) {
        $this->GetMainPicture($part);
      }

      return $data;
    }

    public function GetSegmentByCategoryId( int $catid, int $offset, int $limit, $sortcol, $sortorder, $recursive, $search = null) {
      return $this->GetListByCategoryId($search, $catid, $offset, $limit, $sortcol, $sortorder, $recursive, $search);
    }

    public function GetDetailsById( int $partid ) {

      $query = "SELECT p.*, p.id, p.id_category, p.name, p.instock, p.mininstock, "
        ."COALESCE(f.name, '-') AS footprint, "
        ."s.id AS storelocid, s.name AS storeloc, "
        ."su.name AS supplier_name, c.name AS category_name, "
        ."pic.pict_id_arr, pic.pict_fname_arr, pic.pict_height_arr, pic.pict_width_arr, "
        ."pic.pict_masterpict_arr, pic.tn_id_arr, pic.tn_fname_arr, pic.tn_obsolete_arr, "
        ."pic.tn_t_arr,"
        ."pr.price AS price, "
        ."COALESCE(fpr.pict_fname,'default.png') AS f_pict_fname, "
        ."fpr.pict_width as f_pict_width, "
        ."fpr.pict_height as f_pict_height "
        ."FROM parts p "
        ."LEFT JOIN footprints f ON p.id_footprint = f.id "
        ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
        ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
        ."LEFT JOIN categories c ON p.id_category = c.id "
        ."LEFT JOIN ("
          ."SELECT GROUP_CONCAT(a.id) AS pict_id_arr, a.parent_id, GROUP_CONCAT(a.pict_fname SEPARATOR '/') AS pict_fname_arr, GROUP_CONCAT(a.pict_height) AS pict_height_arr, "
            ."GROUP_CONCAT(a.pict_width) AS pict_width_arr, GROUP_CONCAT(a.pict_masterpict) AS pict_masterpict_arr, GROUP_CONCAT(b.id) AS tn_id_arr, GROUP_CONCAT(b.pict_fname SEPARATOR '/') AS tn_fname_arr, "
            ."GROUP_CONCAT(b.tn_obsolete) AS tn_obsolete_arr, GROUP_CONCAT(b.tn_t) AS tn_t_arr FROM pictures a LEFT JOIN pictures b ON b.tn_pictid = a.id "
          ."WHERE a.pict_type = 'P' GROUP BY parent_id"
        .") pic ON pic.parent_id = p.id " //AND (pic.pict_masterpict = 1 OR pic.pict_) "
        ."LEFT JOIN prices pr ON pr.part_id = p.id "
        ."LEFT JOIN ("
          ."SELECT * FROM pictures WHERE pict_type = 'F'"
        .") fpr ON f.id = fpr.parent_id "
        ."WHERE p.id = $partid "
        ."LIMIT 1";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);

      if( !$res )
      {
        return null;
      }
      $data = $res->fetch_assoc();
      $res->free();

      // Get main picture
      $this->GetMainPicture($data);

      return $data;
    }

    public function GetById( int $partid ) {

      $query = "SELECT * FROM parts WHERE id = $partid";
      $res = $this->db->sql->query($query) or \Log::WarningSQLQuery($query, $this->db->sql);
      $data = $res->fetch_assoc();
      $res->free();

      return $data;
    }

    private function GetMainPicture( &$data ) {
      // Get main picture file
      $idx = 0;
      $pfname = null;
      $tpfname = null;

      // Check for the first selected masterpicture
      if( $data['pict_masterpict_arr'] != null )
      {
        $mpic_flags = explode(',', $data['pict_masterpict_arr'] );
        $idx = array_search('1', $mpic_flags);
        if( $idx === false ) {
          $idx = 0;
        }
      }

      // Get the picture from the array if possible. If no master is selected get the first picture in the array
      if( $data['pict_fname_arr'] != null )
      {
        $pic_fnames = explode('/', $data['pict_fname_arr']);
        if( sizeof($pic_fnames) > 0 ) {
          if( sizeof($pic_fnames) < $idx+1 )
            $pfname = $pic_fnames[0];
          else
            $pfname = $pic_fnames[$idx];

          $pfname = "/img/parts/".$pfname;
        }
      }

      // Get the corresponding thumbnail
      if( $data['tn_fname_arr'] != null ) {
        $tpic_fnames = explode('/', $data['tn_fname_arr']);
        if( sizeof($tpic_fnames) > 0 ) {
          if( sizeof($pic_fnames) < $idx+1 )
            $tpfname = $tpic_fnames[0];
          else
            $tpfname = $tpic_fnames[$idx];

          $tpfname = "/img/parts/".$tpfname;
        }
      }

      // Check for the footprint picture if none was found yet
      if( $pfname === null ) {
        if( $data['f_pict_fname'] != null ) {
          $pfname = "/img/footprint/".$data['f_pict_fname'];
        }
      }

      // Get the default image if still nothing has been found
      if( $pfname === null ) {
        $canEnhanceMainPic = false;
        $pfname = "/img/footprint/default.png";
        $tpfname = $pfname;
      }

      // Thumbnail is the same as main image, if none found yet
      if( $tpfname === null ) {
        $tpfname = $pfname;
      }

      $data["mainPicFile"] = $pfname;
      $data["mainPicThumbFile"] = $tpfname;
    }

  }
} // END NAMESPACE
?>
