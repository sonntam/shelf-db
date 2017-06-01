<?php

namespace ConfigFile {

	// Set all the defaults
	// --------------------

	/**
	 * Configuration relevant settings/properties
	 */
	class Config {
		/** @var string Version number of this configuration file */
		static $VERSION = "1.0";
	}

	/**
	 * Database relevant configuration settings
	 */
	class Database {

		/** @var string SQL Database Hostname */
		static public $HOST = "localhost";
		/** @var string SQL Database username */
		static public $USER = "partdb";
		/** @var string SQL Database password */
		static public $PASSWORD = "";
		/** @var string SQL Database name to use */
		static public $NAME = "partdb";
		/** @var int Port of SQL database */
		static public $PORT = 30154;

	}

	/**
	 * Logging relevant configuration settings
	 */
	class Log {

		/**
		 * One of each debug, info, warning, error
		 * @var string
		 */
		static public $LOGLEVEL = "error";

		static public $LOGDATETIME = false;
	}

}
?>
