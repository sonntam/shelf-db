<?PHP

require_once(__DIR__.'/log.php');
require_once(__DIR__.'/config.php');

/**
 * PartDB-Database singleton class
 */
class PartDatabase
{

  private const VERSION = array("major" => 1, "minor" => 1);

  /**
   * @var mysqli $sql MySQL object
   */
  private $sql;

  private function sql(): mysqli
  {
    return $this->sql;
  }

  /** Constructor */
  function __construct()
  {

  }

  /**
   * Get the singleton instance of PartDatabase
   * @return PartDatabase The singleton instance
   */
  public static function Instance() : PartDatabase
  {
    static $db = null;

    if( is_null($db) )
    {
      $db = new PartDatabase();

      $db->Connect();
      $db->InjectCustomSQL();
      $db->CheckTables();
    }

    return $db;
  }

  /**
   * Get the MySQL singleton instance interface
   */
  public static function InstanceSQL()
  {
    return PartDatabase::Instance()->sql;
  }

  /**
   * Make custom SQL functions available
   */
  private function InjectCustomSQL()
  {
    $this->InjectCustomSQLFromFile( dirname(__DIR__).'/sql/natsort.sql' );
  }

  /**
   * Inject SQL from a FilesystemIterator
   * @param string $filename Path to file containing SQL statements
   */
  public function InjectCustomSQLFromFile(string $filename)
  {
    $sqlc = file_get_contents( $filename );

    if( $sqlc === false )
    {
      Log::Error("Could not find SQL injection file \"$filename\"");
      return;
    }

    $res = $this->sql->multi_query($sqlc) or
      Log::WarningSQLQuery($sqlc, $this->sql);;

    // Free result
    $this->FreeResults();
  }

  /**
   * Close all results from SQL multi_query
   * @return [type] [description]
   */
  public function FreeResults()
  {
    while( $this->sql->more_results() ) {
      if( $res = $this->sql->use_result() ) {
        $res->close();
      }
      $this->sql->next_result();
    }
  }

  /**
   * Connect to MySQL database
   */
  private function Connect()
  {
    $this->sql = new mysqli(
      ConfigFile\Database::$HOST,
      ConfigFile\Database::$USER,
      ConfigFile\Database::$PASSWORD,
      "",
      ConfigFile\Database::$PORT
    );

    if( $this->sql->connect_errno ) {
      Log::Error("Error connecting to SQL database: Error #".
        $this->sql->connect_errno." -> ".$this->sql->connect_error
      );
      throw new Exception("Error connecting to SQL database.");
    } else {
      Log::Info("Connected to MySQL database ".ConfigFile\Database::$USER."@"
        .ConfigFile\Database::$HOST.":".ConfigFile\Database::$PORT);
    }

    // Set utf8 charset
    Log::Debug("Initial MySQL charset is \"".$this->sql->character_set_name()."\"");

    Log::Debug("Changing MySQL charset to utf-8");
    if( !$this->sql->set_charset("utf8") ) {
      Log::Error("Error loading MySQL character set utf-8: ". $this->sql->error);
    }

    // Select database
    if( !$this->sql->select_db(ConfigFile\Database::$NAME) )
    {
      Log::Warning("Could not find SQL database \"".ConfigFile\Database::$NAME."\". Trying to create it...");
      $this->CreateDatabase();
    }
    else {
      Log::Info("Selected database `".ConfigFile\Database::$NAME."`");
    }
  }

  /**
   * Create a new database if it could not be found
   */
  private function CreateDatabase()
  {
    Log::Debug("Creating database ".ConfigFile\Database::$NAME.".");
    $query = "CREATE DATABASE `".$this->sql->escape_string( ConfigFile\Database::$NAME )."`";
    $this->sql->query($query) or
      Log::WarningSQLQuery($query,$this->sql);

    // Select the newly created database
    if( !$this->sql->select_db(ConfigFile\Database::$NAME) )
    {
      Log::Error("Could not select the created SQL database \"".ConfigFile\Database::$NAME."\".");
      throw new Exception("Could not select SQL database \"".ConfigFile\Database::$NAME."\".");
    }
  }

  /**
   * Get the version info of the SQL database or return false if it cannot be determined
   */
  public function GetDatabaseVersion()
  {
    $query = "SELECT `value` FROM info WHERE `key`='version';";
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query,$this->sql);

    if($res === false || ($data = $res->fetch_array()) === null)
    {
      // No version table exist at all... return empty version
      return false;
    }

    // Split by major and minor version
    $version        = $data["value"];
    $version_parts  = explode(".", $version );

    // Check format
    if( count($version_parts) != 2 ) return false;

    $version = null;
    $version["major"] = (int)$version_parts[0];
    $version["minor"] = (int)$version_parts[1];

    return $version;
  }

  /**
   * Get the program version info
   */
  public function GetProgramVersion()
  {

    // Split by major and minor version
    $version        = ConfigFile::VERSION;
    $version_parts  = explode(".", $version );

    // Check format
    if( count($version_parts) != 2 ) return false;

    $version = null;
    $version["major"] = (int)$version_parts[0];
    $version["minor"] = (int)$version_parts[1];

    return $version;
  }

  /**
   * Check if currently installed database matches this software version.
   * If not, apply updating strategy or make a clean install if no version
   * is installed.
   */
  private function CheckTables()
  {
    // First check if the table version matches this version
    $version = $this->GetDatabaseVersion();

    if( $version === false )
    {
      Log::Info("No installed PartDatabase SQL tables found.");
      $this->CreateTables();
      return;
    }

    // Check if this software is outdated
    if( $version["major"] > PartDatabase::VERSION["major"]
      || ( $version["major"] == PartDatabase::VERSION["major"]
        && $version["minor"] > PartDatabase::VERSION["minor"]
        )
    ) {
      Log::Error("The installed SQL database is too new for this software: "
        .getversionstring($version)." versus ".getversionstring(PartDatabase::VERSION)."."
      );
      throw new Exception("Installed SQL database is too new for this software.");
    }

    if( sortn($version) == sortn(PartDatabase::VERSION) )
    {
      Log::Info("Using database version ".getversionstring($version));
    }
    // TODO Update strategies
  }

  private function CreateTables()
  {
    Log::Info("Creating PartDatabase SQL tables...");
    $this->InjectCustomSQLFromFile("./sql/createtables.sql");
  }

  public function GetCategoryDirectChildrenFromId( int $catid = 0 ) {
    // SELECT c.*, COUNT(p.id) FROM categories c LEFT JOIN parts p ON p.id_category = c.id WHERE c.id = 2 GROUP BY c.id
    $query = "SELECT c.id, c.name, COUNT(p.id) as partcount FROM categories c LEFT JOIN parts p ON p.id_category = c.id WHERE c.parentnode = $catid GROUP BY c.id ORDER BY c.name ASC";

    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

    $children = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();

    return $children;
  }

  public function GetFootprints($id = null) {

    if( $id === null ) {
      // Get All
      $query = "SELECT f.id, f.name, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM footprints f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'F' ORDER BY udf_NaturalSortFormat(f.name, 10, \".,\")";
    } else {
      $query = "SELECT f.id, f.name, COALESCE(p.pict_fname,'default.png') as pict_fname, p.id as pict_id FROM footprints f LEFT JOIN pictures p ON p.parent_id = f.id AND p.pict_type = 'F' WHERE f.id = $id ORDER BY udf_NaturalSortFormat(f.name, 10, \".,\")";
    }

    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

    $children = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();

    return $children;
  }

  public function GetPartsSegmentByCategoryId( int $catid, int $offset, int $limit, $sortcol, $sortorder, $recursive = false) {

    if( $recursive ) {
      $catids = $this->GetCategorySubcategoryIds( $catid, true );
    } else {
      $catids = array($catid);
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
      ."CONCAT(p.instock,'/',p.mininstock) AS partnum, f.name AS footprint, "
      ."s.id AS storelocid, s.name AS storeloc, "
      ."su.name AS supplier_name, c.name AS category_name, "
      ."pic.pict_id_arr, pic.pict_fname_arr, pic.pict_height_arr, pic.pict_width_arr, "
      ."pic.pict_masterpict_arr, pic.tn_id_arr, pic.tn_fname_arr, pic.tn_obsolete_arr, "
      ."pic.tn_t_arr,"
      ."pr.price AS price, "
      ."COALESCE(fpr.pict_fname,'default.png') AS f_pict_fname "
      ."FROM parts p "
      ."LEFT JOIN footprints f ON p.id_footprint = f.id "
      ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
      ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
      ."LEFT JOIN categories c ON p.id_category = c.id "
      ."LEFT JOIN ("
        ."SELECT GROUP_CONCAT(a.id) AS pict_id_arr, a.parent_id, GROUP_CONCAT(a.pict_fname) AS pict_fname_arr, GROUP_CONCAT(a.pict_height) AS pict_height_arr, "
          ."GROUP_CONCAT(a.pict_width) AS pict_width_arr, GROUP_CONCAT(a.pict_masterpict) AS pict_masterpict_arr, GROUP_CONCAT(b.id) AS tn_id_arr, GROUP_CONCAT(b.pict_fname) AS tn_fname_arr, "
          ."GROUP_CONCAT(b.tn_obsolete) AS tn_obsolete_arr, GROUP_CONCAT(b.tn_t) AS tn_t_arr FROM pictures a LEFT JOIN pictures b ON b.tn_pictid = a.id "
        ."WHERE a.pict_type = 'P' GROUP BY parent_id"
      .") pic ON pic.parent_id = p.id " //AND (pic.pict_masterpict = 1 OR pic.pict_) "
      ."LEFT JOIN prices pr ON pr.part_id = p.id "
      ."LEFT JOIN pictures fpr ON f.id = fpr.parent_id "
      ."WHERE p.id_category IN (".join(',', $catids ).") "
      ."ORDER BY udf_NaturalSortFormat($sortname, 10, \".,\") $sortorder LIMIT $limit OFFSET $offset";
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);
    if( !$res )
    {
      return null;
    }
    $data = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();

    return $data;
  }

  public function GetStorelocations($id = null) {

    // Extract Part
    if( $id === null ) {
      $query = "SELECT * FROM storeloc ORDER BY udf_NaturalSortFormat(name, 10, \".,\")";
    } else {
      $query = "SELECT * FROM storeloc WHERE id = $id";
    }
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);
    $data = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();

    return $data;
  }

  public function GetPartDetailById( int $partid ) {

    $query = "SELECT p.*, p.id, p.id_category, p.name, p.instock, p.mininstock, "
      ."f.name AS footprint, "
      ."s.id AS storelocid, s.name AS storeloc, "
      ."su.name AS supplier_name, c.name AS category_name, "
      ."pic.pict_id_arr, pic.pict_fname_arr, pic.pict_height_arr, pic.pict_width_arr, "
      ."pic.pict_masterpict_arr, pic.tn_id_arr, pic.tn_fname_arr, pic.tn_obsolete_arr, "
      ."pic.tn_t_arr,"
      ."pr.price AS price, "
      ."COALESCE(fpr.pict_fname,'default.png') AS f_pict_fname "
      ."FROM parts p "
      ."LEFT JOIN footprints f ON p.id_footprint = f.id "
      ."LEFT JOIN storeloc s ON p.id_storeloc = s.id "
      ."LEFT JOIN suppliers su ON p.id_supplier = su.id "
      ."LEFT JOIN categories c ON p.id_category = c.id "
      ."LEFT JOIN ("
        ."SELECT GROUP_CONCAT(a.id) AS pict_id_arr, a.parent_id, GROUP_CONCAT(a.pict_fname) AS pict_fname_arr, GROUP_CONCAT(a.pict_height) AS pict_height_arr, "
          ."GROUP_CONCAT(a.pict_width) AS pict_width_arr, GROUP_CONCAT(a.pict_masterpict) AS pict_masterpict_arr, GROUP_CONCAT(b.id) AS tn_id_arr, GROUP_CONCAT(b.pict_fname) AS tn_fname_arr, "
          ."GROUP_CONCAT(b.tn_obsolete) AS tn_obsolete_arr, GROUP_CONCAT(b.tn_t) AS tn_t_arr FROM pictures a LEFT JOIN pictures b ON b.tn_pictid = a.id "
        ."WHERE a.pict_type = 'P' GROUP BY parent_id"
      .") pic ON pic.parent_id = p.id " //AND (pic.pict_masterpict = 1 OR pic.pict_) "
      ."LEFT JOIN prices pr ON pr.part_id = p.id "
      ."LEFT JOIN pictures fpr ON f.id = fpr.parent_id "
      ."WHERE p.id = $partid "
      ."LIMIT 1";
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);
    if( !$res )
    {
      return null;
    }
    $data = $res->fetch_assoc();
    $res->free();

    return $data;
  }

  public function GetPartById( int $partid ) {

    $query = "SELECT * FROM parts WHERE id = $partid";
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);
    $data = $res->fetch_assoc();
    $res->free();

    return $data;
  }

  public function GetCategorySubcategoryIds( int $rootcatid, $includeroot = false ) {

    if( $includeroot ) {
      $catids = array($rootcatid);
    } else {
      $catids = array();
    }

    $query = "SELECT id FROM categories WHERE parentnode = $rootcatid ORDER BY name ASC";
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

    $data = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();

    $data = array_map( function($el) {
      return (int)($el['id']);
    }, $data);

    $catids = array_merge($catids, $data);

    foreach( $data as $catid ) {
      $subcats = $this->GetCategorySubcategoryIds( $catid );
      $catids = array_merge($catids,$subcats);
    }

    return $catids;
  }

  public function GetNumberOfPartsByCategoryId( int $catid = 0, $recursive = false ) {

    $partcount = 0;

    $query = "SELECT COUNT(id) as partcount FROM parts WHERE id_category = $catid";

    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

    $data = $res->fetch_assoc();
    $res->free();

    $partcount += $data['partcount'];

    // recurse
    if($recursive)
    {
      // Get child categories
      $query = "SELECT id FROM categories WHERE parentnode = $catid";
      $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);
      $children = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      foreach( $children as $category )
      {
        $partcount += $this->GetNumberOfPartsByCategoryId( $category['id'], true );
      }
    }

    return $partcount;
  }

  public function GetParentCategoryFromId( int $catid = 0 ) {
    $query = "SELECT id, name FROM categories WHERE id = (SELECT parentnode FROM categories WHERE id = $catid)";

    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

    $parent = $res->fetch_assoc();
    $res->free();

    if( $parent === null )
    {
      $parent['id'] = 0;
      $parent['name'] = "root";
    }

    return $parent;
  }

  public function GetCategoriesAsArray( int $baseid = 0, bool $withparent = false ) {

    // Get parent item
    if( $withparent && $baseid != 0 )
    {
        $query = "SELECT c.id, c.name, COUNT(p.id) as partcount FROM categories c LEFT JOIN parts p ON p.id_category = c.id WHERE c.id = $baseid GROUP BY c.id";

        $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

        $parent = $res->fetch_assoc();
        $res->free();

        $parent['id'] = intval($parent['id']);
        $parent['partcount']  = intval($parent['partcount']);
    }

    // Get all subitems
    $tree = $this->GetCategoryDirectChildrenFromId($baseid);

    if( $withparent && $baseid != 0 )
    {
      // Append
      $newtree[0] = $parent;
      $newtree[0]['children'] = $tree;
      $tree = $newtree;
    }

    // Build tree recursively
    foreach( $tree as &$node )
    {
        $node['id'] = intval($node['id']);
        $node['partcount']  = intval($node['partcount']);

        $children = $this->GetCategoriesAsArray( (int)($node['id']), false );
        if( $children ) {
          $node['children'] = $children;
          for( $i = 0; $i < count($children); $i++ ) {
            $node['partcount'] += intval($children[$i]['partcount']);
          }
        }
    }

    return $tree;
  }

  public function GetCategoryNameFromId( int $id ) {
    $query = "SELECT `name` FROM `categories` WHERE `id` = $id";

    $res = $this->sql->query($query) or Log::WarningSQLQuery($query, $this->sql);

    $name = $res->fetch_assoc();
    $res->free();

    return $name["name"];
  }

  public function SetCategoryNameById( int $id, string $name ) {
    $name  = $this->sql->real_escape_string( $name );
    $query = "UPDATE `categories` SET `name` = '$name' WHERE `id` = $id";

    $res = $this->sql->query($query) ;

    if( $res === true ) {
      // Everything OK
      return true;
    } else {
      // Error occured
      Log::WarningSQLQuery($query, $this->sql);

      return false;
    }
  }

  public function AddCategoryToParentById( int $parentid, string $name ) {
    $name = $this->sql->real_escape_string( $name );
    $query = "INSERT INTO `categories` (`name`, `parentnode`) VALUES ('$name', $parentid)";

    $res = $this->sql->query($query);

    if( $res === true ) {
      $newid = $this->sql->insert_id;

      return $newid;

    } else {
      Log::WarningSQLQuery($query, $this->sql);
      return false;
    }

  }

  public function MoveCategoryToParentById( int $id, int $newparentid ) {

    $query = "UPDATE `categories` SET `parentnode` = $newparentid WHERE `id` = $id";

    $res = $this->sql->query($query) ;

    if( $res === true ) {
      // Everything OK
      return true;
    } else {
      // Error occured
      Log::WarningSQLQuery($query, $this->sql);

      return false;
    }
  }
}

$pdb = PartDatabase::Instance();
$db  = PartDatabase::InstanceSQL();

?>
