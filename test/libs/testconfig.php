<?php
namespace phpsec;


/**
 * Required Files
 */
require_once (__DIR__ . "../../../libs/db/dbmanager.php");
require_once (__DIR__ . "../../../libs/crypto/confidentialstring.php");


try
{
	$username = confidentialString(':/X6NSUlAagxmmLNWRZBA8fyJbmQZmAB7VcgzHHfTxwA=');
	$password = confidentialString(':bpsY8XdMOZdO32Jnoh7wqh1Og3ogQkIs3e6k8Kvk1J0=');
	
	DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP',$username,$password));	//create a new Db handler.
}
catch (\Exception $e)
{
	echo $e->getMessage();
}

?>