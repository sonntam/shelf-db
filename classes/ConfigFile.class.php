<?php

namespace {

	require_once(__DIR__.'/config.defaults.php');
	require_once(dirname(__DIR__).'/lib/namespacefinder.php');
	require_once(dirname(__DIR__).'/lib/utils.php');

	/**
	 * Class for JSON configuration file
	 * @param string $filename File to attach this config file instance to
	 */
	class ConfigFile {

		/**
		 * @var string VERSION Version info
		 */
		public const VERSION = "1.2";

		/**
		 * @var string $filename Path and filename to JSON config file
		 */
		private $filename = "";

		/**
		 * Constructor
		 * @param string $filename File to attach this config file instance to
		 */
		private function __construct(string $filename)	{
			$this->filename = $filename;
			if( !$this->ReadFromFile($filename) )
			{
				// Config file does not exist, write a default OutOfBoundsException
				Log::Warning("Config file $filename does not exist. Writing a default config file to $filename...");
				$this->SaveToNewFile($filename);
			}
		}

		/**
		 * Get the singleton instance for the default configuration file
		 */
		public static function Instance()
		{
			static $theInstance = null;

			if( is_null($theInstance) )
			{
				$theInstance = new ConfigFile(dirname(__DIR__).'/config/config.json');
			}

			return $theInstance;
		}

		/**
		 * Read configuration settings from a specific file and apply them
		 * @param string $filename Path to JSON config file
		 * @return bool Returns false if file not found, true otherwise
		 */
		public function ReadFromFile(string $filename) : bool
		{
			if( !isset($filename) || $filename == "" )
			{
				Log::Warning("Using default \"config.json\" configuration file.");
				$filename = dirname(__DIR__)."/config/config.json";
			}

			if( !file_exists($filename) )
			{
				Log::Error("Config file \"$filename\" does not exist. Doing nothing.");
				return false;
			}

			// Check access rights
			$ac = fileperms($filename);
			if( ($ac & 0x07) != 0 ) {
				Log::Warning("Config file \"$filename\" is publicly visible (mod=$ac). Trying to change access rights..." );
				$newac = $ac & ~0x7;
				if( !chmod( $filename, $newac ) ) {
					throw new Exception("Could not change access rights of config file. Please try to do it manually. Exiting.");
					exit;
				} else {
					Log::Info("Config file \"$filename\" chmod from $ac to $newac successful.");
				}
			}

			$this->filename = $filename;

			$cfg = json_decode( file_get_contents($filename), true ) or Log::Error("Invalid configuration file at \"$filename\"");
			Log::Debug("Read config file contents: ".var_export($cfg,true));

			// Check version
			$version = get($cfg['config']['version'], null);
			if( $version === null ) {
				// Invalid
				Log::Error("Configuration file $filename is invalíd. Using defaults only.");
				return true;
			}

			// Set all fields
			foreach($cfg as $key => $parameters)
			{
				foreach($parameters as $name => $value)
				{
						$class = "ConfigFile\\".ucfirst($key);
						$property = $name;
						if( property_exists($class, $property) ) {
							Log::Debug("Setting $class::$".$property." = \"$value\"");
							$class::${$property} = $value;
						} else {
							Log::Warning("Setting $key\\$name does not exist in this software. Ignoring...");
						}
				}
			}

			// Check if any fields were missing. If so add the default value
			// Get Sections of config file
			$nsf = new NameSpaceFinder();

			$nsc = $nsf->getClassesOfNameSpace("ConfigFile");

			// Traverse each Sections
			$missing = false;
			foreach ($nsc as $key => $sectionpath) {
				// The section name itself
				$section = lcfirst( endn(explode("\\",$sectionpath)) );
				$class = new ReflectionClass($sectionpath);
				$props = $class->getStaticProperties();

				if( !array_key_exists($section, $cfg) ) {
					Log::Warning("Configuration file is missing section \"$section\". I'm adding the default values...");
					$cfg[$section] = $props;
					$missing = true;
				} else {
					foreach( $props as $propKey => $propVal ) {
						if( !array_key_exists($propKey, $cfg[$section] ) ) {
							Log::Warning("Configuration file is missing value \"$section::$propKey\". Setting it to current value \"$propVal\" (probably the default).");
							$cfg[$section][$propKey] = $propVal;
							$missing = true;
						}
					}
				}
			}

			// Write missing config options to file
			if( $missing ) {
				$this->SaveToNewFile($this->filename, $cfg);
			}

			// Check if versions differ
			if( $version != ConfigFile::VERSION )
			{
				Log::Warning("Configuration file $filename version is $version which "
					." differs from this software version ".ConfigFile::VERSION.". "
					."Not all configuration properties may be set correctly or are set "
					."to default values - check the documentation!");
				Log::Warning("Extending the original configuration file with the default new settings.");

				// Set current version
				ConfigFile\Config::$version = ConfigFile::VERSION;

				// Overwrite
				$this->SaveToNewFile($this->filename);
			}

			return true;

		}

		/**
		 * Save the current configuration to a specific FilesystemIterator
		 * @param string $filename Path to JSON config file
		 */
		public function SaveToNewFile(string $filename, $cfg = null)
		{
			if( !isset($filename) || $filename == "" )
			{
				Log::Error("Invalid path.");
				return;
			}

			// Get Sections of config file
			$nsf = new NameSpaceFinder();

			$nsc = $nsf->getClassesOfNameSpace("ConfigFile");

			// Traverse each Sections
			if( empty($cfg) ) {

				$cfg = [];
				foreach ($nsc as $key => $sectionpath) {
					// The section name itself
					$section = lcfirst( endn(explode("\\",$sectionpath)) );
					$class = new ReflectionClass($sectionpath);
					$props = $class->getStaticProperties();

					// Formatted configurations fit for JSON export
					$props_f = $props;
					/*foreach ($props as $propname => $propvalue) {
						$propname_l = strtolower($propname);
						if( $propname != $propname_l )
						{
							$props_f[$propname_l] = $propvalue;
							unset($props_f[$propname]);
						}
					}*/

					$cfg[$section] = $props_f;
				}
			}

			Log::Debug("Saving JSON: ".var_export($cfg,true));

			// Save to file
			$json = json_encode($cfg, JSON_PRETTY_PRINT);

			file_put_contents($filename,$json) or Log::Error("Failed writing config to file \"$filename\"");
		}
	}

	$cfg = ConfigFile::Instance();

	//$cfg->ReadFromFile("");
	//$cfg->SaveToNewFile("config2.json");

}
?>
