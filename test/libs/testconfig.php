<?php
namespace phpsec;


/**
 * Required Files
 */
require_once(__DIR__ . "/../../libs/db/dbmanager.php");


try {
	$username = "travis";
	$password = "";

	DatabaseManager::connect(new DatabaseConfig('pdo_mysql', 'OWASP', $username, $password, "127.0.0.1")); //create a new Db handler.
} catch (\Exception $e) {
	echo $e->getMessage();
}

?>