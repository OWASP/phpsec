<?php

	namespace phpsec;
        
        require_once (__DIR__ . "/../core/functions.php");

	class DatabaseException extends \Exception {}
	class DatabaseUnsupportedAdapterException extends DatabaseException {}
	class DatabaseNotSet extends DatabaseException {}

	/**
	 * This class is not being used currently.
	 * Might be needed later for the framework.
	 * Just include this file and use as following
	 * $a = DatabaseManager::connect (new DatabaseConfig('pdo_mysql','owasp','root','password'));
	 * $b = $a->SQL('SELECT * FROM SESSION');
	 */

	/**
	 * Abstraction layer for accessing database using several adapters.
	 * Intended support for pdo_mysql, pdo_sqlite, pdo_pgsql for now.
	 */

	require_once (__DIR__ . '/adapter/base.php');

	class DatabaseManager {

		public static $db;

		static function connect (DatabaseConfig $dbConfig) {
			try {
				if(file_exists(__DIR__ . "/adapter/{$dbConfig->adapter}.php"))
					require_once (__DIR__ . "/adapter/{$dbConfig->adapter}.php");
				else
					throw new DatabaseUnsupportedAdapterException("{$dbConfig->adapter} is not a supported database adapter.");
			}
			catch (DatabaseUnsupportedAdapterException $e) {
                                echof($e->getMessage());
				return false;
			}
			$db_class = "phpsec\Database_{$dbConfig->adapter}";
			return self::$db = new $db_class($dbConfig->dbname, $dbConfig->username, $dbConfig->password, $dbConfig->host);
		}

	}
	
	function SQL()
	{
		if (DatabaseManager::$db == NULL)
			throw new DatabaseNotSet("ERROR: Database is not set/configured properly.");
		
		$args = func_get_args();
		$query = array_shift($args);
		
		if ( count($args) == 0)
			return DatabaseManager::$db->SQL($query, array());
		
		if ( is_array($args[0]) )
			return DatabaseManager::$db->SQL($query, $args[0]);
		else
			return DatabaseManager::$db->SQL($query, $args);
	}

?>