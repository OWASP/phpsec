<?php

require_once(__DIR__ . "/../../libs/db/dbmanager.php");

//include necessary files to initiate DB connection and to call the SQL function
try
{
	$config = include(__DIR__ . "/../../libs/config.php");

        $dbname = $config['DBNAME'];
        $username = $config['DBUSER'];
	$password = $config['DBPASS'];

	\phpsec\DatabaseManager::connect(new \phpsec\DatabaseConfig('pdo_mysql', $dbname, $username, $password, "127.0.0.1")); //create a new Db handler.
}
catch (\Exception $e)
{
	echo $e->getMessage();
}

?>
