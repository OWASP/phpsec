<?php
namespace phpsec;



/**
 * Required Files
 */
require_once(__DIR__ . "/base.php");



/**
 * PDO_SQLite wrapper class.
 * Extends the parent DatabaseModel class.
 */
class Database_pdo_sqlite extends DatabaseModel
{



	/**
	 * Constructor of this class. Used for creating a new DB connection and to set PDO modes.
	 * @param \PDO			The PDO object
	 */
	public function __construct ()
	{
		$args = func_get_args ();	//get all arguments to this function
		if (isset($args[0]) && get_class($args[0]) === "PDO")	//If only one argument is present and if that argument is a PDO object and is directly passed in the argument, then do not create a new PDO object. Just use the old object
			$this->dbh = $args[0];
		else
		{
			$dbConfig = new DatabaseConfig ('pdo_sqlite',NULL,NULL,NULL);	//get a new DB connection
			parent::__construct ($dbConfig);			//call the DatabaseModel's constructor to pass the DatabaseConfig object to initialize a new object
			$this->dbh = new \PDO ("sqlite::memory:");	//create a new PDO object
		}
		$this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);	//set PDO attributes
	}



	/**
	 * Destructor of the class. Destroys the PDO object
	 */
	public function __destruct ()
	{
		if (isset($this->dbh))
		{
			$this->dbh = NULL;
		}
	}



	/**
	 * Function to prepare the query. It prepares an SQL statement to be executed by the PDOStatement::execute() method.
	 * @param string $query					The string to be executed
	 * @return \phpsec\DatabaseStatement_pdo_sqlite		Returns the object of type \phpsec\DatabaseStatement_pdo_sqlite
	 */
	function prepare ($query)
	{
		return new DatabaseStatement_pdo_sqlite ($this, $query);
	}
}



/**
 * PDO_SQLite prepared statements class.
 * Extends the parent DatabaseStatementModel class.
 */
class DatabaseStatement_pdo_sqlite extends DatabaseStatementModel
{



	/**
	 *
	 * @param \phpsec\Database_pdo_sqlite	$db		The object of class \phpsec\Database_pdo_sqlite
	 * @param string			$query		The query to be executed
	 */
	public function __construct (Database_pdo_sqlite $db, $query)
	{
		parent::__construct ($db, $query);
	}
}

?>