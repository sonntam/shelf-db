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

}
?>
