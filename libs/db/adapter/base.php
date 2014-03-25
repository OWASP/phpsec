<?php
namespace phpsec;



/**
 * Required Files
 */
require_once __DIR__."/../dbmanager.php";



/**
 * DatabaseConfig class
 * A single object for all database configuration options
 */
class DatabaseConfig
{



	/**
	 * Adapter to be used for DB connection. E.g. pdo_mysql to use mysql DB in pdo format
	 * @var string
	 */
	public $adapter = NULL;



	/**
	 * Name of the DB to connect to.
	 * @var string
	 */
	public $dbname = NULL;



	/**
	 * Username of the DB used for connection
	 * @var string
	 */
	public $username = NULL;



	/**
	 * Password for the DB used for connection
	 * @var string
	 */
	public $password = NULL;



	/**
	 * Host where the DB resides
	 * @var string
	 */
	public $host = NULL;



	/**
	 * Constructor of the class to get and initialize all the user's credentials
	 * @param string $adapter	The adapter of the DB such as "pdo_mysql"
	 * @param string $dbname	The name of the DB
	 * @param string $username	The username of the DB for connection
	 * @param string $password	The password of the DB for connection
	 * @param string $host		The host where the DB resides
	 */
	function __construct ($adapter, $dbname, $username, $password, $host="127.0.0.1")
	{
		$this->adapter = $adapter;
		$this->dbname = $dbname;
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
	}
}



/**
 * DatabaseModel class.
 * Intended as parent for all database wrapper classes.
 */
abstract class DatabaseModel
{


	/**
	 * Object of type \phpsec\DatabaseConfig
	 * @var \phpsec\DatabaseConfig
	 */
	public $dbConfig = NULL;



	/**
	 * PDO object
	 * @var \PDO
	 */
	public $dbh = NULL;



	/**
	 * Constructor of the class. Used to get the DB configuration
	 * @param \phpsec\DatabaseConfig $dbConfig	The object of type \phpsec\DatabaseConfig that contains all the necessary credentials to connect to the DB
	 */
	public function __construct ($dbConfig)
	{
		$this->dbConfig = $dbConfig;
	}



	/**
	 * Abstract function to prepare the query. It prepares an SQL statement to be executed by the PDOStatement::execute() method. Should return a PDOStatement object.
	 */
	abstract protected function prepare ($query);



	/**
	 * Function to return the id of the last row that was inserted in the DB
	 * @return int		ID of the last row inserted
	 */
	public function lastInsertId ()
	{
		return $this->dbh->lastInsertId();
	}



	/**
	 * Function to execute an SQL statement
	 * @param string $query		The query to be executed
	 * @return int | array | null	It may return last insert ID, or row count, or an array containing the results, or null.
	 * @throws DatabaseNotSet	Thrown in case trying to execute a SQL statement when connection to DB is not set
	 */
	public function SQL ($query)
	{
		//If the DB connection is still empty, then throw an error
		if ($this->dbh === NULL) {
			throw new DatabaseNotSet("ERROR: Database is not set/configured properly.");
		}

		$args = func_get_args ();	//get the arguments to this function
		array_shift ($args);		//remove the first argument as that contains the actual "QUERY"
		$statement = $this->prepare ($query);	//Prepares an SQL statement to be executed by the PDOStatement::execute() method. Returns a PDOStatement object.

		if (!empty ($args[0]))		//If arguments are passed, then check if the first argument is an array. If yes, then that array contains all the arguments
		{
			if (is_array ($args[0]))
			{
				$statement->execute ($args[0]);		//Execute the statement with this array
			}
			elseif (count ($args) >= 1)		//If there are more than 1 arguments, then call the "bindall" function of the class DatabaseStatementModel to call PDO's function bindValue to bind the parameters to the ? in the query
			{
				call_user_func_array (array ($statement, "bindAll"), $args);
				$statement->execute ();
			}
		}
		else
		{
			$statement->execute ();		//If no arguments are passed, then execute the query with empty arguments
		}

		$type = substr (trim (strtoupper ($query)), 0, 3);	//get the first three letters of the query
		if ($type == "INS")	//If the query is of insert type
		{
			$res = $this->LastInsertId();	//Then return the last insert ID
			if ($res == 0)		//If nothing is inserted, then return the number of rows affected by the corresponding PDOStatement object.
			{
				return $statement->rowCount();
			}

			return $res;
		}
		elseif ($type == "DEL" or $type == "UPD")	//If the query is delete or update, then returns the number of rows affected by the corresponding PDOStatement object.
		{
			return $statement->rowCount();
		}
		elseif ($type == "SEL")				//If query is select type, then return all the rows returned by the DB
		{
			return $statement->fetchAll();
		}

		return null;	//If none of the above types match, then probable that query is wrong and thus return null
	}



	/**
	 * Function to call PDO::query() to execute an SQL statement in a single function call.
	 * @param string $query			Statement to execute
	 * @return \PDOStatement		Returns the result set (if any) returned by the statement as a PDOStatement object.
	 */
	public function query($query)
	{
		return $this->dbh->query($query);
	}



	/**
	 * Function to call PDO::exec() to execute an SQL statement in a single function call.
	 * @param string $query			Statement to execute
	 * @return int				Returns the number of rows affected by the statement.
	 */
	public function exec($query)
	{
		return $this->dbh->exec($query);
	}
}



/**
 * DatabaseStatementModel class.
 * Intended as parent for all database prepared statement classes.
 */
abstract class DatabaseStatementModel
{



	/**
	 * Object of type DatabaseModel
	 * @var \phpsec\DatabaseModel
	 */
	protected $db;



	/**
	 * The query to be executed
	 * @var string
	 */
	protected $query;



	/**
	 * Parameters to the query to be executed
	 * @var array
	 */
	protected $params;



	/**
	 * PDOStatement object.
	 * @var \PDOStatement
	 */
	protected $statement;



	/**
	 * Constructor of the class to set DB connection, query and statement
	 * @param \phpsec\DatabaseModel $db
	 * @param string $query
	 */
	public function __construct ($db, $query)
	{
		$this->db = $db;
		$this->query = $query;
		$this->statement = $db->dbh->prepare ($query);
	}



	/**
	 * Destructor of the class
	 */
	public function __destruct ()
	{
		if (isset($this->statement))
		{
			$this->db = NULL;
			$this->query = NULL;
			$this->params = NULL;
			$this->statement = NULL;
		}
	}



	/**
	 * Fetches a row from a result set associated with a PDOStatement object.
	 * @return \PDO::FETCH_ASSOC		The fetch_style parameter determines how PDO returns the row.
	 */
	public function fetch ()
	{
		return $this->statement->fetch (\PDO::FETCH_ASSOC);
	}



	/**
	 * Fetches all rows from a result set associated with a PDOStatement object.
	 * @return \PDO::FETCH_ASSOC		The fetch_style parameter determines how PDO returns the row.
	 */
	public function fetchAll()
	{
		return $this->statement->fetchAll (\PDO::FETCH_ASSOC);
	}



	/**
	 * Binds a value to a corresponding named or question mark placeholder in the SQL statement that was used to prepare the statement.
	 */
	public function bindAll ()
	{
		$params = func_get_args ();
		$this->params = $params;
		$i = 0;
		foreach ($params as &$param) {
			$this->statement->bindValue (++$i, $param);	//call the PDO's bindValue method to bind the value
		}
	}



	/**
	 * Execute the prepared statement.
	 * @return boolean	Returns TRUE on success or FALSE on failure.
	 */
	public function execute ()
	{
		$args = func_get_args ();	//get all user arguments
		if (!empty ($args[0]) && is_array ($args[0]))	//If the argument contains an array that contains all the parameters, then execute the statement with all those parameters
			return $this->statement->execute ($args[0]);
		else
			return $this->statement->execute ();	//If the argument does not contain any parameter, then execute the statement without any parameters
	}



	/**
	 * Returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement executed by the corresponding PDOStatement object.
	 * @return int		Number of rows affected
	 */
	public function rowCount ()
	{
		return $this->statement->rowCount ();
	}
}

?>