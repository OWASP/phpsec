<?php
namespace phpsec;


/**
 * Required Files
 */
require_once(__DIR__ . "/../../libs/db/dbmanager.php");

try {
	$config = include(__DIR__ . "/../../libs/config.php");

        $dbname = $config['DBNAME'];
        $username = $config['DBUSER'];
	$password = $config['DBPASS'];

	DatabaseManager::connect(new DatabaseConfig('pdo_mysql', $dbname, $username, $password, "127.0.0.1")); //create a new Db handler.
} catch (\Exception $e) {
	echo $e->getMessage();
}

?>