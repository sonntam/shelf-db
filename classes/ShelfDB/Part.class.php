<?php

namespace ShelfDB {

  class Part {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    public function FormatPrice( $priceVal ) {
      $str = "";
      $str = number_format( $priceVal,
        \ConfigFile\Currency::$numDecimals,
        \ConfigFile\Currency::$decimal,
        \ConfigFile\Currency::$thousandsDelimiter
      );
      if( strtolower(\ConfigFile\Currency::$symbolPosition) == 'behind' ) {
        $str = $str . \ConfigFile\Currency::$symbol;
      } else {
        $str = \ConfigFile\Currency::$symbol . $str;
      }
      return $str;
    }

    public function CreateQRCode( $partId ) {
      $qr = \QRCode::getMinimumQRCode($partId, QR_ERROR_CORRECT_LEVEL_Q);

      $im = $qr->createImage(\ConfigFile\QRCode::$pixelWidth, \ConfigFile\QRCode::$qrMargin);
      if( !$im ) {
        \Log::Error("Error creating QR code.");
        return;
      }

      $data = "data:image/".strtolower(\ConfigFile\QRCode::$dataType).';base64,';

      ob_start();
      switch(strtolower(\ConfigFile\QRCode::$dataType)) {
        case 'gif':
          imagegif($im);
          break;
        case 'png':
          imagepng($im);
          break;
      }
      $data = $data.base64_encode(ob_get_clean());

      @imagedestroy($im);

      return $data;
    }

    public function AllReplaceFootprintId( int $oldid, int $newid ) {
      $query = "UPDATE parts SET id_footprint = $newid WHERE id_footprint = $oldid;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      // Update history
      $this->db()->History()->Add(0, 'P', 'edit', 'footprint', array(
        "id" => $oldid,
        "name" => $this->db()->Footprint()->GetNameById($oldid)
      ), array(
        "id" => $newid,
        "name" => $this->db()->Footprint()->GetNameById($newid)
      ) );

      return $res;
    }

    public function AllReplaceSupplierId( int $oldid, int $newid ) {
      $query = "UPDATE parts SET id_supplier = $newid WHERE id_supplier = $oldid;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      // Update history
      $this->db()->History()->Add(0, 'P', 'edit', 'supplier', array(
        "id" => $oldid,
        "name" => $this->db()->Supplier()->GetNameById($oldid)
      ), array(
        "id" => $newid,
        "name" => $this->db()->Supplier()->GetNameById($newid)
      ) );

      return $res;
    }

    public function AllReplaceStorelocationId( int $oldid, int $newid ) {
      $query = "UPDATE parts SET id_storeloc = $newid WHERE id_storeloc = $oldid;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      // Update history
      if( $this->db()->affected_rows > 0 ) {
        $this->db()->History()->Add(0, 'P', 'edit', 'storeLocation', array(
          "id" => $oldid,
          "name" => $this->db()->StoreLocation()->GetNameById($oldid)
        ), array(
          "id" => $newid,
          "name" => $this->db()->StoreLocation()->GetNameById($newid)
        ) );
      }

      return $res;
    }

    private function ExplodeSearchString($search) {
      // Is this a single column-based or global search?
      if( !is_array($search) ) {
        // Do global search
        $clauses = str_getcsv( $search, " ");
        foreach( $clauses as &$el) {
          $escapedSearch = '%'.$this->db()->sql->real_escape_string($el).'%';

          $el = "("
            ."f.name LIKE '$escapedSearch' OR "
            ."s.name LIKE '$escapedSearch' OR "
            ."su.name LIKE '$escapedSearch' OR "
            ."c.name LIKE '$escapedSearch' OR "
            ."p.name LIKE '$escapedSearch' OR "
            ."p.comment LIKE '$escapedSearch')";
        }
        return array("(" . join(" OR ", $clauses) . ")");

      } else if(isset($search["groupOp"])) {

        $fnSQLSearchString = function($searchLevel, $groupOp = null) use (&$fnSQLSearchString) {

          if( array_key_exists("groupOp", $searchLevel) ) {
            // We have a rules array to process
            return $fnSQLSearchString($searchLevel["rules"], $searchLevel["groupOp"]);
          } else {
            // We have an array of rules
            if( empty($groupOp) ) throw new Exception("Error while building SQL search string.");
            $sqlRules = array();
            foreach( $searchLevel as $rule ) {
              if( array_key_exists("groupOp", $rule) ) {
                // Another level
                $sqlRules[] = $fnSQLSearchString( $rule["rules"], $rule["groupOp"] );
              } else {
                $operatorFn = TranslateJqGridToMySQL($rule["operator"]);
                $escapedSearch = $this->db()->sql->real_escape_string($rule["data"]);
                if( strtolower($rule["name"]) === 'any') {
                  $sqlRules[] = "("
                    ."f.name LIKE '%$escapedSearch%' OR "
                    ."s.name LIKE '%$escapedSearch%' OR "
                    ."su.name LIKE '%$escapedSearch%' OR "
                    ."c.name LIKE '%$escapedSearch%' OR "
                    ."p.name LIKE '%$escapedSearch%' OR "
                    ."p.comment LIKE '%$escapedSearch%')";
                } else {
                  $col        = TranslateJqGridColumnToMySQL($rule["name"]);
                  $sqlRules[] = "(".$operatorFn( $col, $escapedSearch ).")";
                }
              }
            }
            return "(".join(" $groupOp ",$sqlRules).")";
          }
        };

        $search = array($fnSQLSearchString($search));

        return $search;
      }
    }

    private function GetDataColumnById(int $id, $column ) {
      $query = "SELECT $column FROM parts WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        $data = $res->fetch_assoc();
        $res->free();

        return $data[$column];
      }

      return null;
    }

    private function SetDataColumnById(int $id, $column, $data) {
      // Get current data
      $oldData = $this->GetDataColumnById( $id, $column );

      if( $oldData === null ) return false;
      if( $oldData == $data ) return array('oldData' => $data);

      $ecdata = $this->db()->sql->real_escape_string($data);
      $query = "UPDATE parts SET $column = '$ecdata' WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( $res ) {
        // Add history
        $this->db()->History()->Add($id,'P','edit',$column, $oldData, $data);
        return array('oldData' => $oldData);
      }

      return false;
    }

    public function SetPartNumberById( int $id, $newNumber ) {
      return $this->SetDataColumnById( $id, "supplierpartnr", $newNumber );
    }

    public function SetNameById( int $id, $newName ) {
      if( trim($newName) == "" )
        return false;

      return $this->SetDataColumnById( $id, "name", $newName );
    }

    public function GetInStockById( int $id ) {
      return $this->GetDataColumnById($id, "instock" );
    }

    public function SetTotalStockById( int $id, int $newTotalStock ) {

      $inStock = $this->GetInStockById($id);

      if( $newTotalStock < $inStock )
        return false;

      return $this->SetDataColumnById( $id, "totalstock", $newTotalStock );
    }

    public function SetInStockById( int $id, int $newInStock ) {
      return $this->SetDataColumnById( $id, "instock", $newInStock );
    }

    public function SetMinInStockById( int $id, int $newMinInStock ) {
      return $this->SetDataColumnById( $id, "mininstock", $newMinInStock );
    }

    public function SetCommentById( int $id, $newComment ) {
      return $this->SetDataColumnById( $id, 'comment', $newComment );
    }

    public function SetPriceById( int $id, $price ) {
      // Getg old price
      $oldPrice = $this->GetPriceById( $id );

      if( !$oldPrice ) { // None has been given yet
        $query = "INSERT INTO prices (part_id, ma, price, t) VALUES ($id, 1.0, $price, NOW());";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

        if( !$res )
          return false;

        $newId = $this->db()->sql->insert_id;

        $this->db()->History()->Add($id, "P", "add", "price", null, array(
          "priceId" => $newId,
          "price" => $price ) );

      } else {
        $query = "UPDATE prices SET price = $price, ma = 1.0, t = NOW() WHERE part_id = $id;";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

        if( !$res )
          return false;

        $this->db()->History()->Add($id, "P", "edit", "price", $oldPrice, $price);
      }

      return true;
    }

    public function SetStorageLocationById( int $id, $storelocId ) {

      if( !$storelocId ) return false;

      // Check if supplier EXISTS
      $newStoreLocation = $this->db()->StoreLocation()->GetById($storelocId);
      if( !$newStoreLocation ) return false;

      $oldData = $this->SetDataColumnById( $id, "id_storeloc", $storelocId );
      if( $oldData ) {
        if( $oldData['oldData'] == $storelocId ) return true;
        $oldStoreLocation = $this->db()->StoreLocation()->GetById($oldData['oldData']);
        $this->db()->History()->Add($id, 'P', 'edit', 'storelocation',
          $oldStoreLocation['name'], $newStoreLocation['name'] );

        return true;
      } else {
        return false;
      }
    }

    public function SetCategoryById( int $id, $categoryId ) {

      if( !$categoryId ) return false;

      // Check if category EXISTS
      $newCategory = $this->db()->Category()->GetById($categoryId);
      if( !$newCategory ) return false;

      $oldData = $this->SetDataColumnById( $id, "id_category", $categoryId );

      if( $oldData ) {
        if( $oldData['oldData'] == $categoryId ) return true;
        $oldCategory = $this->db()->Category()->GetById($oldData['oldData']);
        $this->db()->History()->Add($id, 'P', 'edit', 'category',
          $oldCategory['name'], $newCategory['name'] );

        return true;
      } else {
        return false;
      }
    }

    public function GetPriceById( int $id ) {

      $query = "SELECT price, ma FROM prices WHERE part_id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
        return false;

      $data = $res->fetch_assoc();
      $res->free();

      if( !$data )
        return null;

      return $data["price"] * $data["ma"];
    }

    public function SetFootprintById( int $id, $footprintId ) {

      if( !$footprintId ) return false;

      // Check if supplier EXISTS
      $newFootprint = $this->db()->Footprint()->GetById($footprintId);
      if( !$newFootprint ) return false;

      $oldData = $this->SetDataColumnById( $id, "id_footprint", $footprintId );

      if( $oldData ) {
        if( $oldData['oldData'] == $footprintId ) return true;
        if( $oldData['oldData'] > 0 ) {
          $oldFootprint = $this->db()->Footprint()->GetById($oldData['oldData']);
        } else {
          $oldFootprint = array("name" => "none");
        }
      } else {
        return false;
      }
      $this->db()->History()->Add($id, 'P', 'edit', 'footprint',
        $oldFootprint['name'], $newFootprint['name'] );

      return true;
    }

    public function SetSupplierById( int $id, $supplierId ) {

      if( !$supplierId ) return false;

      // Check if supplier EXISTS
      $newSupplier = $this->db()->Supplier()->GetById($supplierId);
      if( !$newSupplier ) return false;

      if( $oldData = $this->SetDataColumnById( $id, "id_supplier", $supplierId ) ) {
        $oldSupplier = $this->db()->Supplier()->GetById($oldData['oldData']);
        $this->db()->History()->Add($id, 'P', 'edit', 'supplier', $oldSupplier['name'], $newSupplier['name'] );

        return true;
      } else {
        return false;
      }
    }

    public function DeleteById(int $id) {
      // First try to get it
      $fp = $this->GetDetailsById($id);
      if( !$fp ) return false;

      $query = "DELETE FROM parts WHERE id = $id;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      if( !$res )
        return false; // Database my be inconsistent because footrprints have already been replaced

      // Update history
      $this->db()->History()->Add($id, 'P', 'delete', 'object', $fp, '');

      // Clear category cache as the part count changed
      $this->db()->Category()->DefileCache();

      // Now delete the image
      if( isset($fp['pict_id_arr']) && $fp['pict_id_arr'] ){
        $picIds = explode(';',$fp['pict_id_arr']);
        foreach($picIds as $picId) {
            \Log::Info("Trying to delete the image id = $picId entry for part id = $id");
            $this->db()->Picture()->DeleteById($picId);
        }
      }
      return true;
    }

    private function GetCountByCategoryIdExploded( int $catid = 0, $escapedSearch = null, $recursive = false ) {

      $partcount = 0;
      $clauses = array();

      if( $escapedSearch === null )
        $escapedSearch = array();

      if( $recursive && $catid == 0 ) {
        $recursive = false;
      } else {
        $clauses[] = "p.id_category = $catid";
      }

      $clauses = array_merge($clauses, $escapedSearch);

      $query = "SELECT COUNT(p.id) as partcount FROM parts p "
          ."LEFT JOIN footprints f ON p.id_footprint = f.id "
          ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
          ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
          ."LEFT JOIN categories c ON p.id_category = c.id "
        ."WHERE ". join(' AND ', $clauses) .";";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $data = $res->fetch_assoc();
      $res->free();

      $partcount = $data['partcount'];

      // recurse
      if($recursive)
      {
        // Get child categories
        $query = "SELECT id FROM categories WHERE parentnode = $catid";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
        $children = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        foreach( $children as $category )
        {
          $partcount += $this->GetCountByCategoryIdExploded( $category['id'], $escapedSearch, true );
        }
      }

      return $partcount;

    }

    public function GetCountByStoreLocationId( int $storelocId ) {
      $query = "SELECT COUNT(id) as numParts FROM parts WHERE id_storeloc = $storelocId";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $data = $res->fetch_assoc();
      $res->free();

      return $data['numParts'];
    }

    public function GetCountByCategoryId( int $catid = 0, $search = null, $recursive = false ) {

      $partcount = 0;

      if( $search != null && $search != "" ) {
        $search = $this->ExplodeSearchString($search);
        return $this->GetCountByCategoryIdExploded( $catid, $search, $recursive);
      }

      $query = "SELECT COUNT(p.id) as partcount FROM parts p WHERE p.id_category = $catid";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      $data = $res->fetch_assoc();
      $res->free();

      $partcount += $data['partcount'];

      // recurse
      if($recursive)
      {
        // Get child categories
        $query = "SELECT id FROM categories WHERE parentnode = $catid";
        $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
        $children = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();

        foreach( $children as $category )
        {
          $partcount += $this->GetCountByCategoryId( $category['id'], $search, true );
        }
      }

      return $partcount;
    }

    private function GetSegmentByTypeId( string $type, $id, int $offset, int $limit, $sortcol, $sortorder, $recursive, $search = null) {

      if( $search === null || $search == "" ) {
        $searchFilter = array();
      } else {
        $searchFilter = $this->ExplodeSearchString($search);
      }

      switch($type) {
        case 'category':
          if( $recursive ) {
            $ids = $this->db()->Category()->GetSubcategoryIdsFromId( $id, true );
          } else {
            $ids = array($id);
          }
          $searchFilter[] = "p.id_category IN (".join(',', $ids ).")";
          break;
        case 'storeLocation':
          if( is_array( $id ) ) {
            $searchFilter[] = "p.id_storeloc IN (".join(',',$id).")";
          } else {
            $searchFilter[] = "p.id_storeloc = $id";
          }

          break;
        case 'footprint':
          $searchFilter[] = "p.id_footprint = $id";
          break;
        case 'supplier':
          $searchFilter[] = "p.id_supplier = $id";
          break;
        default:
          return null;
      }

      // Support for multiple sortings
      $sortcols = explode( ",", $sortcol );
      $sortorder = ($sortorder == "desc" ? "DESC" : "ASC");
      $sortOptions = array();
      foreach( $sortcols as $sortcol) {
        $args = explode( " ", trim($sortcol) );
        if( count($args) >= 1 ) {
          $localCol   = $args[0];
          $localOrder = $sortorder;
        } else {
          continue;
        }
        if( count($args) == 2 ) {
          $localOrder = $args[1];
        }

        switch( $localCol )
        {
          case "instock":
            $sortname = "p.instock";
            break;
          case "totalstock":
            $sortname = "p.totalstock";
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
          case "category_name":
            $sortname = "c.name";
            break;
          default:
            $sortname = "p.name";
        }
        $sortOptions[] = "udf_NaturalSortFormat($sortname, 10, \".,\") $localOrder";
      }
      if( count($sortOptions) == 0 ) $sortOptions[] = "udf_NaturalSortFormat(p.name, 10, \".,\")";
      $sortOptions = join(", ", $sortOptions);

      $query = "SELECT p.*, p.id, p.id_category, p.name AS name, p.instock AS instock, p.mininstock AS mininstock, "
        ."CONCAT(p.instock,'/',p.mininstock) AS partnum, COALESCE(f.name,'-') AS footprint, "
        ."s.id AS storelocid, s.name AS storeloc, "
        ."su.name AS supplier_name, c.name AS category_name, "
        ."pr.price AS price, "
        ."COALESCE(mpf.pict_fname, fpr.pict_fname, 'default.png') AS mainPicFileName, "
        ."COALESCE(mtpf.pict_fname, tfpr.pict_fname) AS mainThumbnailPicFileName, "
        ."COALESCE(fpr.pict_fname,'default.png') AS f_pict_fname, "
        ."COALESCE(sup.pict_fname,'default.png') AS su_pict_fname, "
        ."fpr.pict_width as f_pict_width, "
        ."fpr.pict_height as f_pict_height, "
        ."GROUP_CONCAT(pic.id) AS pict_id_arr, pic.parent_id, GROUP_CONCAT(pic.pict_fname SEPARATOR '/') AS pict_fname_arr, GROUP_CONCAT(pic.pict_height) AS pict_height_arr, "
        ."GROUP_CONCAT(pic.pict_width) AS pict_width_arr, GROUP_CONCAT(pic.pict_masterpict) AS pict_masterpict_arr, GROUP_CONCAT(tpic.id) AS tn_id_arr, GROUP_CONCAT(tpic.pict_fname SEPARATOR '/') AS tn_fname_arr, "
        ."GROUP_CONCAT(tpic.tn_obsolete) AS tn_obsolete_arr, GROUP_CONCAT(tpic.tn_t) AS tn_t_arr "
        ."FROM parts p "
        ."LEFT JOIN footprints f ON p.id_footprint = f.id "
        ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
        ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
        ."LEFT JOIN categories c ON p.id_category = c.id "
        ."LEFT JOIN pictures mpf ON mpf.id = ("
          ."SELECT id FROM pictures WHERE parent_id = p.id ORDER BY pict_masterpict DESC, id DESC LIMIT 1"
        .") "
        ."LEFT JOIN pictures mtpf ON mtpf.parent_id = mpf.id AND mtpf.pict_type = 'T' "
        ."LEFT JOIN pictures pic ON pic.parent_id = p.id AND pic.pict_type = 'P' " //AND (pic.pict_masterpict = 1 OR pic.pict_) "
        ."LEFT JOIN pictures tpic ON tpic.parent_id = pic.id AND tpic.pict_type = 'T' "
        ."LEFT JOIN prices pr ON pr.part_id = p.id "
        ."LEFT JOIN pictures fpr ON f.id = fpr.parent_id AND fpr.pict_type = 'F' "
        ."LEFT JOIN pictures sup ON su.id = sup.parent_id AND sup.pict_type = 'SU' "
        ."LEFT JOIN pictures tfpr ON tfpr.parent_id = fpr.id AND tfpr.pict_type = 'T' "
        ."WHERE ".join(" AND ", $searchFilter)." "
        ."GROUP BY p.id ORDER BY $sortOptions LIMIT $limit OFFSET $offset";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      \Log::LogSQLQuery($query);
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
      return $this->GetSegmentByTypeId('category', $catid, $offset, $limit, $sortcol, $sortorder, $recursive, $search );
    }

    public function GetSegmentByFootprintId( int $id, int $offset, int $limit, $sortcol, $sortorder, $recursive, $search = null) {
      return $this->GetSegmentByTypeId('footprint', $id, $offset, $limit, $sortcol, $sortorder, $recursive, $search );
    }

    public function GetSegmentBySupplierId( int $id, int $offset, int $limit, $sortcol, $sortorder, $recursive, $search = null) {
      return $this->GetSegmentByTypeId('supplier', $id, $offset, $limit, $sortcol, $sortorder, $recursive, $search );
    }

    public function GetSegmentByStoreLocationId( $id, int $offset, int $limit, $sortcol, $sortorder, $recursive, $search = null) {
      return $this->GetSegmentByTypeId('storeLocation', $id, $offset, $limit, $sortcol, $sortorder, $recursive, $search );
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
        ."fpr.pict_height as f_pict_height, "
        ."COALESCE(sup.pict_fname,'default.png') AS su_pict_fname "
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
        ."LEFT JOIN ("
          ."SELECT * FROM pictures WHERE pict_type = 'SU'"
        .") sup ON su.id = sup.parent_id "
        ."WHERE p.id = $partid "
        ."LIMIT 1";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

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
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
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
