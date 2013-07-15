<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/db/dbmanager.php";
require_once "../../../libs/core/random.php";
require_once "../../../libs/crypto/confidentialstring.php";

class ConfidentialStringTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * Function to test the functionality of confidentialString()
	 */
	public function testConfidentialString()
	{
		try
		{
			$username = \phpsec\confidentialString(':/X6NSUlAagxmmLNWRZBA8fyJbmQZmAB7VcgzHHfTxwA=');		//username is sensitive. Hence pass this to the function.
			$password = \phpsec\confidentialString(':bpsY8XdMOZdO32Jnoh7wqh1Og3ogQkIs3e6k8Kvk1J0=');	//password is sensitive. Hence pass this to the function.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
		try
		{
			DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP', $username, $password));	//create a new Db handler.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
		//do some SQL calls to check if everything works correctly.
		SQL("INSERT INTO `SESSION_DATA`(`SESSION_ID`, `KEY`, `VALUE`) VALUES (?, ?, ?)", array(  randstr(10), "OWASPOWASPOWASPOWASP", "phpsec") );
		
		$result = SQL("SELECT `KEY` FROM `SESSION_DATA` WHERE `KEY` = ?", array("OWASPOWASPOWASPOWASP"));
		
		SQL("DELETE FROM `SESSION_DATA` WHERE `KEY` = ?", array("OWASPOWASPOWASPOWASP"));
		
		$this->assertTrue($result[0]['KEY'] == "OWASPOWASPOWASPOWASP");
	}
}