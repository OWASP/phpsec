<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once 'template.php';
require_once (__DIR__ . "/../../db/dbmanager.php");



class DBLogs extends Template
{


	/**
	 * Variable to store the configuration file of the user.
	 * @var Array
	 */
	protected $dbConfig = null;



	/**
	 * Constructor function to initiate the object of this class.
	 * @param Array $config
	 */
	public function __construct($config)
	{
		$this->dbConfig = $config;	//store the config file.

		//Make a connection to the DB. DB Credentials are provided inside the configuration file.
		DatabaseManager::connect (new DatabaseConfig($config['ADAPTER'], $config['DBNAME'], $config['USERNAME'], $config['PASSWORD']));	//create a new Db handler.
	}



	/**
	 * Function to write the log messages in DB.
	 * @param Array $args	Array of messages as given by the user to be written in log files.
	 */
	public function log($args)
	{
		$logValues = $this->changeTemplate($args);	//change the user given message appropriate to the template of the log files. This is necessary to maintain consistency among all the log files.

		$noOfEntries = count($this->template);	//get the how many entries are in the template. This is same as number of columns present in the DB and this helps in preparing the SQL statement.

		//Prepare the SQL statement.
		$SQLStatement = "INSERT INTO " . $this->dbConfig['TABLENAME'] . " (";
		foreach ($this->template as $key=>$value)
		{
			$SQLStatement = $SQLStatement . $key . ",";	//Add all the column names.
		}
		$SQLStatement = substr($SQLStatement, 0, -1) . ") VALUES (";
		for($i = 0; $i < $noOfEntries; $i = $i + 1)
		{
			$SQLStatement = $SQLStatement . "?,";	//Add as many "?" as there are number of columns.
		}
		$SQLStatement = substr($SQLStatement, 0, -1) . ")";

		$values = array();
		foreach ($logValues as $k=>$v)
		{
			array_push($values, $v);	//create an array that contains values for all columns.
		}

		SQL($SQLStatement, $values);	//execute the SQL statement.
	}



	/**
	 * Function to change the user given message to the template defined by the user. This makes the log files consistent throughout the application.
	 * @param Array $args	Array of messages as given by the user to be written in log files.
	 * @return Array
	 */
	protected function changeTemplate($args)
	{
		$this->setDefaults();	//set defaults in the log messages. Such as date.

		$myTemplate = $this->template;	//copy the template. This is necessary because we do not want to change the original template.

		$i = 0;
		//check if a value is provided by the user for that entry in the template.
		foreach(  $myTemplate as $key=>$value)
		{
			if (isset($args[$i]) && $args[$i] !== "")
			{
				$myTemplate[$key] = $args[$i];	//if user has provided a value, then overwrite the default value with this user provided value.
			}

			$i = $i + 1;
		}

		//return the message to be stored.
		return $myTemplate;
	}
}

?>