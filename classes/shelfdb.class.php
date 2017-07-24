<?PHP

require_once(__DIR__.'/log.php');
require_once(__DIR__.'/config.php');
require_once(__DIR__.'/shelfdb.part.class.php');
require_once(__DIR__.'/shelfdb.user.class.php');
require_once(__DIR__.'/shelfdb.group.class.php');
require_once(__DIR__.'/shelfdb.history.class.php');
require_once(__DIR__.'/shelfdb.picture.class.php');
require_once(__DIR__.'/shelfdb.supplier.class.php');
require_once(__DIR__.'/shelfdb.category.class.php');
require_once(__DIR__.'/shelfdb.footprint.class.php');
require_once(__DIR__.'/shelfdb.storelocation.class.php');

require_once(__DIR__.'/../lib/qrcode.php');

/**
 * ShelfDB-Database singleton class
 */
class ShelfDatabase
{
  private const VERSION = array("major" => 1, "minor" => 2);

  /**
   * @var mysqli $sql MySQL object
   */
  public $sql;

  private $parts;
  private $categories;
  private $footprints;
  private $storeLocations;
  private $pictures;
  private $suppliers;
  private $users;
  private $history;
  private $groups;

  private function sql(): mysqli
  {
    return $this->sql;
  }

  /** Constructor */
  function __construct()
  {
    $this->parts            = new ShelfDB\Parts($this);
    $this->categories       = new ShelfDB\Categories($this);
    $this->footprints       = new ShelfDB\Footprints($this);
    $this->storeLocations   = new ShelfDB\StoreLocations($this);
    $this->pictures         = new ShelfDB\Pictures($this);
    $this->suppliers        = new ShelfDB\Suppliers($this);
    $this->users            = new ShelfDB\Users($this);
    $this->history          = new ShelfDB\History($this);
    $this->groups           = new ShelfDB\Groups($this);
  }

  public function Parts()           { return $this->parts; }
  public function Categories()      { return $this->categories; }
  public function Footprints()      { return $this->footprints; }
  public function StoreLocations()  { return $this->storeLocations; }
  public function Pictures()        { return $this->pictures; }
  public function Suppliers()       { return $this->suppliers; }
  public function Users()           { return $this->users; }
  public function History()         { return $this->history; }
  public function Groups()          { return $this->groups; }

  /**
   * Get the singleton instance of ShelfDatabase
   * @return ShelfDatabase The singleton instance
   */
  public static function Instance() : ShelfDatabase
  {
    static $db = null;

    if( is_null($db) )
    {
      $db = new ShelfDatabase();

      $db->Connect();
      $db->InjectCustomSQL();
      $db->CheckTables();

      $db->Users()->ResumeSession();
    }

    return $db;
  }

  public static function AbsRoot() {
    return dirname(__DIR__);
  }

  public static function RelRoot() {
    return ShelfDatabase::GetRelativeRoot();
  }

  public static function GetRelativeRoot() {
    static $relRoot = null;
    if( is_null($relRoot) ) {
      $relRoot = '/' . str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__) );
    }
    return $relRoot;
  }

  /**
   * Get the MySQL singleton instance interface
   */
  public static function InstanceSQL()
  {
    return ShelfDatabase::Instance()->sql;
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
      ConfigFile\Database::$host,
      ConfigFile\Database::$user,
      ConfigFile\Database::$password,
      "",
      ConfigFile\Database::$port
    );

    if( $this->sql->connect_errno ) {
      Log::Error("Error connecting to SQL database: Error #".
        $this->sql->connect_errno." -> ".$this->sql->connect_error
      );
      throw new Exception("Error connecting to SQL database.");
    } else {
      Log::Info("Connected to MySQL database ".ConfigFile\Database::$user."@"
        .ConfigFile\Database::$host.":".ConfigFile\Database::$port);
    }

    // Set utf8 charset
    Log::Debug("Initial MySQL charset is \"".$this->sql->character_set_name()."\"");

    Log::Debug("Changing MySQL charset to utf-8");
    if( !$this->sql->set_charset("utf8") ) {
      Log::Error("Error loading MySQL character set utf-8: ". $this->sql->error);
    }

    // Select database
    if( !$this->sql->select_db(ConfigFile\Database::$name) )
    {
      Log::Warning("Could not find SQL database \"".ConfigFile\Database::$name."\". Trying to create it...");
      $this->CreateDatabase();
    }
    else {
      Log::Info("Selected database `".ConfigFile\Database::$name."`");
    }
  }

  /**
   * Create a new database if it could not be found
   */
  private function CreateDatabase()
  {
    Log::Debug("Creating database ".ConfigFile\Database::$name.".");
    $query = "CREATE DATABASE `".$this->sql->escape_string( ConfigFile\Database::$name )."`";
    $this->sql->query($query) or
      Log::WarningSQLQuery($query,$this->sql);

    // Select the newly created database
    if( !$this->sql->select_db(ConfigFile\Database::$name) )
    {
      Log::Error("Could not select the created SQL database \"".ConfigFile\Database::$name."\".");
      throw new Exception("Could not select SQL database \"".ConfigFile\Database::$name."\".");
    }
  }

  /**
   * Get the version info of the SQL database or return false if it cannot be determined
   */
  public function GetDatabaseVersion()
  {
    $query = "SELECT `value` FROM info WHERE `key`='version';";
    $res = $this->sql->query($query) or Log::WarningSQLQuery($query,$this->sql);

    if(!$res || ($data = $res->fetch_array()) === null)
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
      Log::Info("No installed ShelfDatabase SQL tables found.");
      $this->CreateTables();
      return;
    }

    // Check if this software is outdated
    if( $version["major"] > ShelfDatabase::VERSION["major"]
      || ( $version["major"] == ShelfDatabase::VERSION["major"]
        && $version["minor"] > ShelfDatabase::VERSION["minor"]
        )
    ) {
      Log::Error("The installed SQL database is too new for this software: "
        .getversionstring($version)." versus ".getversionstring(ShelfDatabase::VERSION)."."
      );
      throw new Exception("Installed SQL database is too new for this software.");
    }

    if( sortn($version) == sortn(ShelfDatabase::VERSION) )
    {
      Log::Info("Using database version ".getversionstring($version));
    }
    // TODO Update strategies
  }

  private function CreateTables()
  {
    Log::Info("Creating ShelfDatabase SQL tables...");
    $this->InjectCustomSQLFromFile("./sql/createtables.sql");
  }

  public function DeleteOldTempFiles() {

    array_map( function( $filename ) {
        $filedate = filectime( $filename );
        if( $filedate < time() - ConfigFile\FileSystem::$tempFileMaxAgeSecs ) {
          unlink($filename);
        }
    }, glob(dirname(__DIR__) . "/img/tmp/*"));

  }

}

$pdb = ShelfDatabase::Instance();
$db  = ShelfDatabase::InstanceSQL();

?>
