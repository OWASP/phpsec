<?php

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

	require (__DIR__ . '/model.php');

	class DatabaseManager {

		protected static $db;

		static function connect (DatabaseConfig $dbConfig) {
			try {
				if(file_exists("adapter/{$dbConfig->adapter}.php"))
					require (__DIR__ . "/adapter/{$dbConfig->adapter}.php");
				else
					throw new Exception("{$dbConfig->adapter} is not a supported database adapter.");
			}
			catch (Exception $e) {
				echo $e->getMessage();
				return false;
			}
			$db_class = "\Database_{$dbConfig->adapter}";
			return self::$db = new $db_class($dbConfig);
		}

	}

?>