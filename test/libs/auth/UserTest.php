<?php
namespace phpsec;
ob_start();



/**
 * Required Files.
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/core/time.php";


class UserTest extends \PHPUnit_Framework_TestCase
{



	/**
	 * @var User	The object of the user
	 */
	private $obj;



	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose a hashing algo
		User::newUserObject("rash", 'testing', "rac130@pitt.edu"); //create a new user
		User::activateAccount("rash");	//activate the account
		$this->obj = User::existingUserObject("rash", "testing");	//get the existing user object
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

		//Since the account was created moments ago. The difference must not be greater than rougly 5 seconds
		$this->assertTrue( (($currentTime - $creationTime) < 5) && (strlen((string)$creationTime) == 10) );
	}



	/**
	 * Function to test if we can get the correct primary email of the user or not.
	 */
	public function testPrimaryEmail()
	{
		$this->assertTrue($this->obj->getPrimaryEmail($this->obj->getUserID()) == "rac130@pitt.edu");
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
		User::newUserObject("phpsec", "owasp", "rac130@pitt.edu");	//create a new user
		User::activateAccount("phpsec");		//activate the account
		User::lockAccount("phpsec");	//lock this user's account
		$this->assertTrue(User::isLocked("phpsec"));	//test if isLocked() function is working properly

		try
		{
			$testUser = User::existingUserObject("phpsec", "owasp");	//try to create an object of this user
		}
		catch(\phpsec\UserLocked $e)	//This exception must be thrown
		{
			$firstTest = TRUE;	//set the condition to true as exception is thrown

			User::unlockAccount("phpsec");	//unlock the account
			$testUser = User::existingUserObject("phpsec", "owasp");	//try to create an object of this user
			$secondTest = ($testUser->getUserID() === "phpsec");	//now since the account is unlocked, all methods must work properly

			$testUser->deleteUser();	//delete this test User
			$this->assertTrue($firstTest && $secondTest);
		}
	}



	/**
	 * Function to test accessibility if the account is inactive/active.
	 */
	public function testInactive()
	{
		User::newUserObject("phpsec", "owasp", "rac130@pitt.edu");	//create a new user
		try
		{
			$testUser = User::existingUserObject("phpsec", "owasp");		//note that the account is not activated. Hence an exception will be thrown
		}
		catch (UserAccountInactive $e)	//exception must be thrown since the account is inactive
		{
			$this->assertTrue(TRUE);	//since exception is thrown, the test succeded.

			User::activateAccount("phpsec");		//activate the account
			$testUser = User::existingUserObject("phpsec", "owasp");		//note that the account is now active. Hence the object will be created successfully.
			$this->assertTrue($testUser->getUserID() == "phpsec");

			$this->assertTrue(! User::isInactive("phpsec"));

			$testUser->deleteUser();
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

		User::deleteAuthenticationToken();
	}


	/**
	 * Function to test if allows to create a user with an Null ID
	 * @expectedException phpsec\UserIDInvalid
	 */
	public function testUserIDNull()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose a hashing algo
		User::newUserObject(null, 'testing', "rac130@pitt.edu"); //create a new user
	}

	/**
	 * Function to test several userID (newUserObject will use this funcion to determine if throwns a exception
	 */
	public function testUserIDValidInvalid()
	{
		$this->assertTrue(User::isUserIDValid("abcd"));
		$this->assertTrue(User::isUserIDValid("ABCD"));
		$this->assertTrue(User::isUserIDValid("1234"));
		$this->assertTrue(User::isUserIDValid("AbCd"));
		$this->assertTrue(User::isUserIDValid("A1b2C3d4"));
		$this->assertTrue(User::isUserIDValid("0A1b2C3d4"));

		$this->assertFalse(User::isUserIDValid(null));
		$this->assertFalse(User::isUserIDValid(""));
		$this->assertFalse(User::isUserIDValid(" "));
		$this->assertFalse(User::isUserIDValid("A "));
		$this->assertFalse(User::isUserIDValid("A BC D"));
		$this->assertFalse(User::isUserIDValid("A#BC-D"));
		$this->assertFalse(User::isUserIDValid("##$%"));
		$this->assertFalse(User::isUserIDValid("0A1b2C3d4 "));
	}


}
