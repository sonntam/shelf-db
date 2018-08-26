<?php

namespace ConfigFile {

	// Set all the defaults
	// --------------------

	/**
	 * Configuration relevant settings/properties
	 */
	class Config {
		/** @var string Version number of this configuration file */
		static $version = "1.2";

		/** @var bool Enable debugging here */
		static $debug = false;

		/** @var bool Use remote minified css and js source files instead of local ones */
		static $extSources = false;

		/** @var bool Use minified JavaScript and css files */
		static $useMinified = true;
	}

	/**
	 * Database relevant configuration settings
	 */
	class Database {

		/** @var string SQL Database Hostname */
		static public $host = "localhost";
		/** @var string SQL Database username */
		static public $user = "root";
		/** @var string SQL Database password */
		static public $password = "";
		/** @var string SQL Database name to use */
		static public $name = "shelfdb";
		/** @var int Port of SQL database */
		static public $port = 30154;

	}

	/**
	 * Logging relevant configuration settings
	 */
	class Log {

		/**
		 * One of each debug, info, warning, error
		 * @var string
		 */
		static public $logLevel = "error";

		static public $logDateTime = false;

		/** @var bool Enable logging */
		static $enableFileLogging = false;

		/** @var string Path in which logging files will be created */
		static $loggingDir = '';
	}

	/**
	 * Filesystem relevant configuration settings
	 */
	class FileSystem {
		static public $tempFileMaxAgeSecs = 3600;
	}

	/**
	 * QR Codes
	 */
	class QRCode {
		static public $enable     = true;
		static public $pixelWidth = 8;
		static public $qrMargin   = 4;
		static public $dataType   = 'png';
	}

	/**
	 * Currency and price information
	 */
	class Currency {
		static public $symbol = '€';
		static public $symbolPosition = 'behind';
		static public $decimal = ',';
		static public $thousandsDelimiter = '.';
		static public $numDecimals = 2;
	}

	/**
	 * Cache settings
	 */
	 class Cache {
		 static public $folder = __DIR__.'/../cache';
		 static public $expireSeconds = 3600;
	 }

	 /**
	  * Language default
	  */
	 class Language {
		 static public $default = "enEN";
	 }
}
?>
