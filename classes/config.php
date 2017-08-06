<?PHP
# @Date:   2011-05-15T13:51:00+02:00
# @Last modified time: 2017-03-26T20:53:45+02:00



/*
	part-db version 0.1
	Copyright (C) 2005 Christoph Lechner
	http://www.cl-projects.de/

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

	$Id$

	ChangeLog
	02/12/2009
		Split of from lib.php
*/

namespace {
use PMA\libraries\properties\options\items\BoolPropertyItem;

	require_once(__DIR__.'/config.defaults.php');
	require_once(__DIR__.'/log.php');
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
		public function SaveToNewFile(string $filename)
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
