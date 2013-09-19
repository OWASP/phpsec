<?php
namespace phpsec;
ob_start();

/**
 * Required Files.
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/core/random.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/core/time.php";
require_once(__DIR__ . "/../../../libs/crypto/confidentialstring.php");


class UserTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var User
	 */
	private $obj;

	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose a hashing algo.
		$this->obj = User::newUserObject("rash", 'testing', "rac130@pitt.edu"); //create a new user.
	}

	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		$this->obj->deleteUser();
	}

	/**
	 * To check the account creation date.
	 */
	public function testGetAccountCreationDate()
	{
		$currentTime = time("SYS"); //get current time.
		$creationTime = $this->obj->getAccountCreationDate();


		//the current time must be greater than the time it was created.
		$this->assertTrue(($currentTime >= $creationTime) && (strlen((string)$creationTime) == 10));
	}


	/**
	 * To check if the passwords are validated on providing passwords.
	 */
	public function testValidatePassword()
	{
		//provide correct password
		$this->assertTrue($this->obj->verifyPassword('testing'));
		//provide wrong password
		$this->assertFalse($this->obj->verifyPassword("resting"));
	}


	/**
	 * To check if we get object of an existing user.
	 */
	public function testExistingUser()
	{
		$this->obj = User::existingUserObject("rash", 'testing'); //get the object of this user again via this method.

		//try to run validate password function with this new object.
		$this->assertTrue($this->obj->verifyPassword('testing'));
	}


	/**
	 * Function to check if passwords are reset.
	 */
	public function testResetPassword()
	{
		$newPassword = "resting";
		$oldPassword = "testing";

		//verify the password with the new password. Note that the new password is still not set.
		$this->assertFalse($this->obj->verifyPassword($newPassword));
		
		//set the new password.
		$this->obj->resetPassword($oldPassword, $newPassword);

		//verify the password with the new password since now the new password is set.
		$this->assertTrue($this->obj->verifyPassword($newPassword));
	}


	/**
	 * To check if password has expired or not.
	 */
	public function testIsPasswordExpired()
	{
		$currentTime = time("SYS");

		User::$passwordExpiryTime = 1000; //set the password expiry time to 1000.
		time("SET", $currentTime + 5000); //set a new false time that is bound to exceed the expiry limit.

		$this->assertTrue($this->obj->isPasswordExpired());
	}
	
	
	/**
	 * Function to test forceLogIn function.
	 */
	public function testForceLogIn()
	{
		$obj1 = User::forceLogin("rash"); //try to force-login this user.

		$test = $this->obj->getUserID() == $obj1->getUserID(); //check if both of these objects are same.

		$obj1->deleteUser(); //delete the newly created users.

		$this->assertTrue($test);
	}
	
	
	
	/**
	 * Function to test accessibility if the account is locked/unlocked.
	 */
	public function testLocked()
	{
		$testUser = User::newUserObject("phpsec", "owasp", "rac130@pitt.edu");
		$testUser->lockAccount();
		
		try
		{
			User::existingUserObject("phpsec", "owasp");
		}
		catch(\phpsec\UserLocked $e)
		{
			$testUser->deleteUser();
			$firstTest = TRUE;
			
			$testUser->unlockAccount();
			$secondTest = (strlen($testUser->getUserID()) > 1);
			
			$this->assertTrue($firstTest && $secondTest);
		}
	}
	
	
	
	/**
	 * Function to test the "remember-me" functionality.
	 */
	public function testRememberMe()
	{
		//enable the function. This will set the AUTH_ID token in DB.
		User::enableRememberMe($this->obj->getUserID());
		$result = SQL("SELECT `AUTH_ID` FROM `AUTH_TOKENS` WHERE USERID = ?", array($this->obj->getUserID()));//get the token. 
		$_COOKIE['AUTHID'] = $result[0]['AUTH_ID'];	//set the cookie. In real world, this and the above step will be done in browser.
		time("SET", time() + 100000000);	//set the time to some distant future.
		$this->assertFalse(User::checkRememberMe());	//test should fail since the time has expired. Also the AUTH_ID token will be deleted from the DB.
		
		time("RESET");	//reset the clock.
		User::enableRememberMe($this->obj->getUserID());	//enable the function again.
		$result = SQL("SELECT `AUTH_ID` FROM `AUTH_TOKENS` WHERE USERID = ?", array($this->obj->getUserID()));	//get the token.
		$_COOKIE['AUTHID'] = $result[0]['AUTH_ID'];	//set the cookie.
		$this->assertTrue(User::checkRememberMe() === $this->obj->getUserID());	//the test should pass becaue the token is correct and within time-limit.
	}
}
