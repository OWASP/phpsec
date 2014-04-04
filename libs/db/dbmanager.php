<?php
namespace phpsec;



/**
 * Required Files
 */
require_once(__DIR__ . '/adapter/base.php');



/**
 * Parent Exception
 */
class DatabaseException extends \Exception {}



/**
 * Child Exceptions
 */
class DatabaseUnsupportedAdapterException extends DatabaseException {}
class DatabaseNotSet extends DatabaseException {}



class DatabaseManager
{



	/**
	 * Object of DatabaseModel
	 * @var \phpsec\DatabaseModel
	 */
	public static $db = NULL;



	/**
	 * Abstraction layer for accessing database using several adapters. Intended support for pdo_mysql, pdo_sqlite, pdo_pgsql for now.
	 * @param \phpsec\DatabaseConfig $dbConfig		Object of type DatabaseConfig which contains the necessary credentials to connect to DB
	 * @return \phpsec\DatabaseModel			Returns the object of type DatabaseModel that contains the connection to the DB. DatabaseModel is an abstract class that is extended by all the supported adapters
	 * @throws DatabaseUnsupportedAdapterException		Thrown when trying to connect to a DB from an adapter which is not supported yet
	 */
	public static function connect(DatabaseConfig $dbConfig)
	{
		//check if file exists for the adapter that is requested to connect to the DB. IF file does not exists, then that adapter is not supported yet
		if (!file_exists(__DIR__ . "/adapter/{$dbConfig->adapter}.php")) {
			throw new DatabaseUnsupportedAdapterException("ERROR: {$dbConfig->adapter} is not a supported database adapter.");
		}

		require_once(__DIR__ . "/adapter/{$dbConfig->adapter}.php");	//since file exists, include this adapter to make a connection to the DB

		$db_class = "phpsec\\Database_{$dbConfig->adapter}";
		return self::$db = new $db_class($dbConfig->dbname, $dbConfig->username, $dbConfig->password, $dbConfig->host);	//Make a new instance of that adapter's class as they contain necessary functions to manipulate the DB
	}

}



/**
 * A global function for namespace "phpsec"
 * Function to make a SQL query. The query must be in parameterized form. E.g. SQL("SELECT * FROM USER WHERE USERID = ?", $userID); The second argument defines the string that must substitute the question mark given in the query.
 * @param  array		Array containing query and arguments to this function call
 * @return array		Array containg the result of the operation
 * @throws DatabaseNotSet	Thrown when trying to manipulate DB when a connection does not exists
 */
function SQL()
{
	if (DatabaseManager::$db == NULL) {
		throw new DatabaseNotSet("ERROR: Database is not set/configured properly.");
	}

	$args = func_get_args();	//get the arguments supplied to this function
	$query = array_shift($args);	//Take the first argument as that is the QUERY to the DB

	if (count($args) == 0) {	//If there are no arguments left, then run the query without any arguments i.e. pass an empty array as the argument. Such as "SELECT * FROM USER"
		return DatabaseManager::$db->SQL($query, array());
	}

	if (is_array($args[0])) {	//If the remaining argument is itself an array, then pass that array as the argument
		return DatabaseManager::$db->SQL($query, $args[0]);
	} else {	//Else just pass the remaining arguments
		return DatabaseManager::$db->SQL($query, $args);
	}
}
