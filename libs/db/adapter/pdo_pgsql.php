<?php
namespace phpsec;



/**
 * Required Files
 */
require_once(__DIR__ . "/base.php");



/**
 * PDO_PostgreSQL wrapper class.
 * Extends the parent DatabaseModel class.
 */
class Database_pdo_pgsql extends DatabaseModel
{



	/**
	 * Constructor of this class. Used for creating a new DB connection and to set PDO modes.
	 * @param db_name	The name of the DB
	 * @param db_user	The username of the DB for connection
	 * @param db_pass	The password of the DB for connection
	 */
	public function __construct ()
	{
		$args = func_get_args ();	//get all arguments to this function

		if (!isset($args[0]))		//If no arguments are set, then return false
			return false;

		if (count($args) > 1)		//If more than one argument are present, then create a new PDO object
		{
			$dbConfig = new DatabaseConfig ('pdo_pgsql',$args[0],$args[1],$args[2]);	//get a new DB configuration
			parent::__construct ($dbConfig);		//call the DatabaseModel's constructor to pass the DatabaseConfig object to initialize a new object
			$this->dbh = new \PDO ("pgsql:dbname={$dbConfig->dbname};host={$dbConfig->host};",$dbConfig->username,$dbConfig->password);	//create a new PDO object
		}
		else if (get_class($args[0]) === "PDO")		//If only one argument is present and if that argument is a PDO object and is directly passed in the argument, then do not create a new PDO object. Just use the old object
		{
			$this->dbh = $args[0];
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
	 * @return \phpsec\DatabaseStatement_pdo_pgsql		Returns the object of type \phpsec\DatabaseStatement_pdo_pgsql
	 */
	function prepare ($query)
	{
		return new DatabaseStatement_pdo_pgsql ($this, $query);
	}

}



/**
 * PDO_PostgreSQL prepared statements class.
 * Extends the parent DatabaseStatementModel class.
 */
class DatabaseStatement_pdo_pgsql extends DatabaseStatementModel
{



	/**
	 *
	 * @param \phpsec\Database_pdo_pgsql	$db		The object of class \phpsec\Database_pdo_pgsql
	 * @param string			$query		The query to be executed
	 */
	public function __construct (Database_pdo_pgsql $db, $query)
	{
		parent::__construct ($db, $query);
	}
}

?>