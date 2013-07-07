<?php
namespace phpsec;


/**
 * Required Files
 */
require_once "../../../libs/db/dbmanager.php";
require_once '../../../libs/core/random.php';
require_once '../../../libs/core/time.php';
require_once '../../../libs/auth/user.php';
require_once '../../../libs/auth/adv_password.php';



class AdvPasswordTest extends \PHPUnit_Framework_TestCase
{
	protected $userID = "";
	protected $obj = "";
	
	
	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		try
		{
			DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP','root','testing'));	//create DB connection.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
		try
		{
			BasicPasswordManagement::$hashAlgo = "haval256,5";	//choose salting algo.
			$this->userID = User::newUserObject(Rand::generateRandom(10), "testing");	//create a user.
			$this->obj = new AdvancedPasswordManagement($this->userID->getUserID(), "testing");	//create object to AdvancedPasswordManagement class.
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	
	
	/**
	 * Function to check if the temp password expiry functionality is working.
	 */
	public function testCheckIfTempPassExpired()
	{
		try
		{
			//update the temp pass time to current time.
			SQL("UPDATE PASSWORD SET TEMP_TIME = ? WHERE USERID = ?", array(time("SYS"), $this->userID->getUserID()));
			
			$firstTest = $this->obj->checkIfTempPassExpired();	//this check will provide false, since the temp password time has not expired.

			time("SET", 1390706853);	//Now set the time to some distant future time.
			
			$secondTest = $this->obj->checkIfTempPassExpired();	//this check will provide true, since the temp password time has expired.
			
			//Make the temp password time as it was.
			SQL("UPDATE PASSWORD SET TEMP_TIME = ? WHERE USERID = ?", array(0, $this->userID->getUserID()));

			$this->userID->deleteUser();
			
			$this->assertTrue(!$firstTest && $secondTest);
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	
	
	/**
	 * Function to check if the temp Password functionality is working correctly.
	 */
	public function testTempPassword()
	{
		try
		{
			$currentTime = time("SYS");
			
			AdvancedPasswordManagement::$tempPassExpiryTime = 900;
			
			$this->obj->tempPassword();	//this will create a new temp password.
			
			$result = SQL("SELECT TEMP_PASS FROM PASSWORD WHERE USERID = ?", array($this->userID->getUserID()));
			
			//firstTest
			time("SET", $currentTime + 500);	//set future time that has not passed.
			$firstTest = $this->obj->tempPassword("qwert");	//This should return false since the password is wrong. Even though time has not expired.
			
			//secondTest
			time("SET", $currentTime + 500);
			$secondTest = $this->obj->tempPassword($result[0]['TEMP_PASS']);	//This should return true since the pasword is correct and time has not expired.
			
			//thirdTest
			time("SET", $currentTime + 1000);
			$thirdTest = $this->obj->tempPassword($result[0]['TEMP_PASS']);		//This should return false since time has expired.
			
			$this->userID->deleteUser();
			
			$this->assertTrue(!$firstTest && $secondTest && !$thirdTest);
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	
	/**
	 * Function to test if brute force is detected.
	 */
	public function testBruteForce()
	{
		try
		{
			//repetedly provide wrong password.
			for($i = 0; $i < 7; $i++)
			{
				$this->obj = new AdvancedPasswordManagement($this->userID->getUserID(), "resting", true);	//wrong password provided.
			}

			//since an exception is generated in the above loop, the below line won't execute.
			$this->assertTrue(false);
		}
		catch (\Exception $e)
		{
			$this->userID->deleteUser();
			$this->assertTrue(true);	//If exception is generated, then the function worked.
		}
	}
	
	
	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		SQL("DELETE FROM PASSWORD WHERE USERID = ?", array($this->userID->getUserID()));
		$this->userID->deleteUser();
		
		time("RESET");
	}
}

?>
