<?php
namespace phpsec;



/**
 * Required Files
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/core/time.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/auth/adv_password.php";



class AdvPasswordTest extends \PHPUnit_Framework_TestCase
{



	/**
	 * User object of the current user
	 * @var \phpsec\User
	 */
	protected $user;



	/**
	 * Object of the class AdvancedPasswordManagement
	 * @var \phpsec\AdvancedPasswordManagement
	 */
	protected $obj;



	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose salting algo.
		User::newUserObject("rash", 'testing', "rac130@pitt.edu"); //create a user.
		User::activateAccount("rash");	//activate the user account
		$this->user = User::existingUserObject("rash", "testing");	//get the user object
		$this->obj = new AdvancedPasswordManagement($this->user->getUserID(), 'testing'); //create object to AdvancedPasswordManagement class.
	}



	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		$this->user->deleteUser();
		time("RESET");
	}



	/**
	 * Function to check if the temp password expiry functionality is working.
	 */
	public function testCheckIfTempPassExpired()
	{
		//update the temp pass time to current time.
		SQL("UPDATE PASSWORD SET TEMP_TIME = ? WHERE USERID = ?", array(time("SYS"), $this->user->getUserID()));

		$this->assertFalse(  AdvancedPasswordManagement::checkIfTempPassExpired($this->user->getUserID())); //this check will provide false, since the temp password time has not expired.

		time("SET", time() + 1000000); //Now set the time to some distant future time.
		$this->assertTrue(AdvancedPasswordManagement::checkIfTempPassExpired($this->user->getUserID())); //this check will provide true, since the temp password time has expired.
	}



	/**
	 * Function to check if the temp Password functionality is working correctly.
	 */
	public function testTempPassword()
	{
		$currentTime = time("SYS");
		AdvancedPasswordManagement::$tempPassExpiryTime = 900;

		$temp_pass = AdvancedPasswordManagement::tempPassword($this->user->getUserID()); //this will create a new temp password.

		//firstTest
		time("SET", $currentTime + 500); //set future time that has not passed.
		$this->assertFalse(AdvancedPasswordManagement::tempPassword($this->user->getUserID(), "qwert")); //This should return false since the password is wrong. Even though time has not expired.

		//secondTest
		time("SET", $currentTime + 500);
		$this->assertTrue(AdvancedPasswordManagement::tempPassword($this->user->getUserID(), $temp_pass)); //This should return true since the password is correct and time has not expired.

		//thirdTest
		time("SET", $currentTime + 500);
		$this->assertFalse(AdvancedPasswordManagement::tempPassword($this->user->getUserID(), $temp_pass)); //This should return false since the above temp_pass has already been used once, so its expired.


		$temp_pass = AdvancedPasswordManagement::tempPassword($this->user->getUserID()); //this will create a new temp password.

		//fourthTest
		time("SET", time() + 1000);
		$this->assertFalse(AdvancedPasswordManagement::tempPassword($this->user->getUserID(), $temp_pass)); //This should return false since the time has expired.
	}



	/**
	 * Function to test if brute force is detected when passwords are provided continously.
	 */
	public function testBruteForceForFastPasswordGuessing()
	{
		try
		{
			//repeatedly provide wrong password.
			for ($i = 0; $i < 7; $i++)
			{
				$this->obj = new AdvancedPasswordManagement($this->user->getUserID(), "resting", true); //wrong password provided.
			}
		}
		catch (BruteForceAttackDetectedException $e)
		{
			$this->assertTrue(TRUE);	//True since BruteForceAttackDetectedException was thrown
		}
	}



	/**
	 * Function to test if brute force is detected when failed attempts are done in intervals. e.g. a bot guesses password after every 2 seconds in attempt to fool the system that this is a legit attempt
	 */
	public function testBruteForceForSlowPasswordGuessing()
	{
		try
		{
			//repeatedly provide wrong password.
			for ($i = 0; $i < 7; $i++)
			{
				sleep(2);	//Sleep for some time so that the mechanism can be fooled.
				$this->obj = new AdvancedPasswordManagement($this->user->getUserID(), "resting", true); //wrong password provided.
			}
		}
		catch (BruteForceAttackDetectedException $e)
		{
			$this->assertTrue(TRUE);	//True since BruteForceAttackDetectedException was thrown
		}
	}
}
