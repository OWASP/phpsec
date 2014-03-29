<?php
namespace phpsec;
ob_start();



/**
 * Required Files.
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/auth/usermanagement.php";
require_once __DIR__ . "/../../../libs/core/random.php";



class UserManagementTest extends \PHPUnit_Framework_TestCase
{



	/**
	 * Function to test createUser, deleteUser and userExists functions.
	 */
	public function testUser_Create_Delete_Exists()
	{
		UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user
		User::activateAccount("owasp1");		//activate the user's account
		$userObj = UserManagement::logIn("owasp1", "owasp");	//get the user object

		$firstTest = UserManagement::userExists("owasp1"); //test user that exists.
		$secondTest = UserManagement::userExists("owasp2"); //test user that does NOT exists.

		UserManagement::deleteUser("owasp1"); //delete the created user.

		$this->assertTrue($firstTest && !$secondTest);
	}



	/**
	 * Function to test the total number users present in the DB.
	 */
	public function testUserCount()
	{
		//create two users.
		UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user.
		User::activateAccount("owasp1");
		$userObj1 = UserManagement::logIn("owasp1", "owasp");

		UserManagement::createUser("owasp2", "owasp", "rac130@pitt.edu"); //create a user.
		User::activateAccount("owasp2");
		$userObj2 = UserManagement::logIn("owasp2", "owasp");

		$count = UserManagement::userCount();

		//delete the newly created users.
		UserManagement::deleteUser("owasp1");
		UserManagement::deleteUser("owasp2");

		//total number of users must be 2.
		$this->assertTrue($count === 2);
	}



	/**
	 * Function to test forceLogIn function.
	 */
	public function testForceLogIn()
	{
		UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user.
		User::activateAccount("owasp1");
		$obj1 = UserManagement::logIn("owasp1", "owasp");

		$obj2 = UserManagement::forceLogIn("owasp1"); //try to force-login this user.

		$test = ($obj1->getUserID() === $obj2->getUserID()); //check if both of these objects are same.

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($test);
	}



	/**
	 * Function to check log-in function.
	 */
	public function testLogIn()
	{
		UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user.
		User::activateAccount("owasp1");
		$obj1 = UserManagement::logIn("owasp1", "owasp");

		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$firstTest = ($obj2->getUserID() != NULL);

		try
		{
			$obj3 = UserManagement::logIn("owasp1", "wrongPassword"); //try to log in to the same user using wrong password.
			$secondTest = FALSE;	//exception will be thrown, hence this part will not execute.
		}
		catch (WrongPasswordException $e)
		{
			$secondTest = TRUE;
		}

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($firstTest && $secondTest);
	}



	/**
	 * Function to check logOut function.
	 */
	public function testLogOut()
	{
		UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user.
		User::activateAccount("owasp1");
		$obj1 = UserManagement::logIn("owasp1", "owasp");

		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj3 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.

		//set session variables to imitate real cookies.
		$randomValue = randstr(32);
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array($randomValue, time(), time(), $obj3->getUserID()));
		$_COOKIE['SESSIONID'] = $randomValue;

		UserManagement::logOut($obj3); //log-out the user from this device. This should delete the session from the DB

		$firstTest = ($obj2->getUserID() != NULL);	//since this object is "not" logged out, this would still work

		$result = SQL("SELECT * FROM SESSION");
		$secondTest = (count($result) == 0);

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($firstTest && $secondTest);
	}



	/**
	 * Function to test the function logOutFromALLDevices
	 */
	public function testLogOutFromAllDevices()
	{
		UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user.
		User::activateAccount("owasp1");
		$obj1 = UserManagement::logIn("owasp1", "owasp");

		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj3 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.

		//set session variables to imitate real cookies.
		$randomValue = randstr(32);
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array($randomValue, time(), time(), $obj3->getUserID()));
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array(randstr(32), time(), time(), $obj3->getUserID()));
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array(randstr(32), time(), time(), $obj3->getUserID()));
		$_COOKIE['sessionid'] = $randomValue;

		UserManagement::logOutFromAllDevices($obj1->getUserID());	//This will delete all the sessions from the DB

		$result = SQL("SELECT * FROM SESSION");
		$Test = (count($result) == 0);

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($Test);
	}
}