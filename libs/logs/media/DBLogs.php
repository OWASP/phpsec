<?php
namespace phpsec;

require_once 'template.php';
require_once (__DIR__ . "/../../db/dbmanager.php");

class DBLogs extends Template
{
	protected $dbConfig = null;
	
	public function __construct($config)
	{
		$this->dbConfig = $config;
		
		DatabaseManager::connect (new DatabaseConfig($config['adapter'], $config['dbname'], $config['username'], $config['password']));	//create a new Db handler.
	}
	
	public function log($args)
	{
		$logValues = $this->changeTemplate($args);
		
		$noOfEntries = count($this->template);
		$SQLStatement = "INSERT INTO LOGS (";
		foreach ($this->template as $key=>$value)
		{
			$SQLStatement = $SQLStatement . $key . ",";
		}
		$SQLStatement = substr($SQLStatement, 0, -1) . ") VALUES (";
		for($i = 0; $i < $noOfEntries; $i = $i + 1)
		{
			$SQLStatement = $SQLStatement . "?,";
		}
		$SQLStatement = substr($SQLStatement, 0, -1) . ")";
		
		$values = array();
		foreach ($logValues as $k=>$v)
		{
			array_push(&$values, $v);
		}
		
		SQL($SQLStatement, $values);
	}
	
	protected function changeTemplate($args)
	{
		$this->setDefaults();
		
		$myTemplate = $this->template;
		
		$i = 0;
		foreach(  $myTemplate as $key=>$value)
		{
			if (isset($args[$i]) && $args[$i] !== "")
			{
				$myTemplate[$key] = $args[$i];
			}
			
			$i = $i + 1;
		}
		
		return $myTemplate;
	}
}

?>