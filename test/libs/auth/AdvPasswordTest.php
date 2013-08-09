<?php
namespace phpsec;


/**
 * Required Files
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/core/random.php";
require_once __DIR__ . "/../../../libs/core/time.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/auth/adv_password.php";


class AdvPasswordTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var User
	 */
	protected $userID;

	/**
	 * @var AdvancedPasswordManagement
	 */
	protected $obj;

	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose salting algo.
		$this->userID = User::newUserObject(randstr(10), 'testing'); //create a user.
		$this->obj = new AdvancedPasswordManagement($this->userID->getUserID(), 'testing'); //create object to AdvancedPasswordManagement class.
	}

	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		$this->userID->deleteUser();

		time("RESET");
	}


	/**
	 * Function to check if the temp password expiry functionality is working.
	 */
	public function testCheckIfTempPassExpired()
	{
		//update the temp pass time to current time.
		SQL("UPDATE PASSWORD SET TEMP_TIME = ? WHERE USERID = ?", array(time("SYS"), $this->userID->getUserID()));

		$this->assertFalse($this->obj->checkIfTempPassExpired()); //this check will provide false, since the temp password time has not expired.

		time("SET", 1390706853); //Now set the time to some distant future time.

		$this->assertTrue($this->obj->checkIfTempPassExpired()); //this check will provide true, since the temp password time has expired.
	}


	/**
	 * Function to check if the temp Password functionality is working correctly.
	 */
	public function testTempPassword()
	{
		$currentTime = time("SYS");

		AdvancedPasswordManagement::$tempPassExpiryTime = 900;

		$this->obj->tempPassword(); //this will create a new temp password.

		$result = SQL("SELECT TEMP_PASS FROM PASSWORD WHERE USERID = ?", array($this->userID->getUserID()));

		//firstTest
		time("SET", $currentTime + 500); //set future time that has not passed.
		$this->assertFalse($this->obj->tempPassword("qwert")); //This should return false since the password is wrong. Even though time has not expired.

		//secondTest
		time("SET", $currentTime + 500);
		$this->assertTrue($this->obj->tempPassword($result[0]['TEMP_PASS'])); //This should return true since the password is correct and time has not expired.

		//thirdTest
		time("SET", $currentTime + 1000);
		$this->assertFalse($this->obj->tempPassword($result[0]['TEMP_PASS'])); //This should return false since time has expired.
	}


	/**
	 * Function to test if brute force is detected.
	 *
	 * @expectedException \phpsec\BruteForceAttackDetectedException
	 */
	public function testBruteForce()
	{
		//repeatedly provide wrong password.
		for ($i = 0; $i < 7; $i++) {
			$this->obj = new AdvancedPasswordManagement($this->userID->getUserID(), "resting", true); //wrong password provided.
		}
	}

}
