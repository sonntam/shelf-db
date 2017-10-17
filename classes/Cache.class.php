<?php
namespace {

  require_once(__DIR__.'/config.php');

  class Cache {

    /**
		 * Get the singleton instance for the default cache
		 */
		public static function Instance()
		{
			static $theInstance = null;

			if( is_null($theInstance) )
			{
				$theInstance = new Cache();
			}

			return $theInstance;
		}

    /**
		 * Constructor
		 */
		private function __construct()	{

		}

    public function sanitizeCacheName(string $cacheName) {
      return $cacheName;
    }

    public function isCached(string $cacheName) {

      $files = $this->getAllCached($cacheName);

      $curTime = time();

      $foundValid = null;

      foreach( $files as $file ) {
        if( $file["time"] > $curTime || $file["time"] <= $curTime - \ConfigFile\Cache::$expireSeconds || $foundValid ) {
          // Delete cache as it lies in the future or is too old
          @unlink( $file["fullpath"]);
        } else {
          $foundValid = $file["fullpath"];
        }
      }

      return $foundValid;
    }

    public function getCached(string $cacheName) {

      $cacheFile = $this->isCached($cacheName);

      if( $cacheFile )
        return unserialize(file_get_contents($cacheFile));

      return null;
    }

    public function storeCached(string $cacheName, $obj) {

      $cacheName = $this->sanitizeCacheName($cacheName);

      if( !is_dir(\ConfigFile\Cache::$folder) ) {
        mkdir( \ConfigFile\Cache::$folder, 0777, true );
      }

      $fileName =  \ConfigFile\Cache::$folder . "/" . $cacheName . "_" . time();

      file_put_contents($fileName, serialize($obj) );
      Log::Debug("Stoting cache \"$cacheName\" to file $fileName");
    }

    public function getAllCached(string $cacheName) {
      $cacheName = $this->sanitizeCacheName($cacheName);

      // Search for cache file
      $files = glob( \ConfigFile\Cache::$folder . "/".$cacheName."_*" );
      sort( $files, SORT_NATURAL);
      $files = array_reverse($files);
      $files = array_map( function($x) {
        $parts = explode("_", $x);
        return array("time" => $parts[1], "fullpath" => $x );
      }, $files);

      return $files;
    }

    public function deleteCached(string $cacheName) {

      $files = $this->getAllCached($cacheName);

      foreach($files as $file) {
        @unlink( $file["fullpath"]);
      }

    }
  }
}
?>
