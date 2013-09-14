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
	 * Function to test createUser, deleteUser and userExists functionalities.
	 */
	public function testUser_Create_Delete_Exists()
	{
		$userObj = UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a user.

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
		$userObj1 = UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu");
		$userObj2 = UserManagement::createUser("owasp2", "owasp", "rac130@pitt.edu");

		$count = UserManagement::userCount();

		//delete the newly created users.
		UserManagement::deleteUser("owasp1");
		UserManagement::deleteUser("owasp2");

		//total number of users must be 2.
		$this->assertTrue($count == 2);
	}


	/**
	 * Function to test forceLogIn function.
	 */
	public function testForceLogIn()
	{
		$obj1 = UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a new user.
		$obj2 = UserManagement::forceLogIn("owasp1"); //try to force-login this user.

		$test = $obj1->getUserID() == $obj2->getUserID(); //check if both of these objects are same.

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($test);
	}


	/**
	 * Function to check deviceLoggedIn, logOutFromAllDevices function.
	 */
	public function testLogIn()
	{
		$obj1 = UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a new user.
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
		$obj1 = UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a new user.
		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj3 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		
		//set session variables to imitate real cookies.
		$randomValue = randstr(32);
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array($randomValue, time(), time(), $obj3->getUserID()));
		$_COOKIE['sessionid'] = $randomValue;
		
		UserManagement::logOut($obj3); //log-out the user from 1 device.

		$firstTest = ($obj2->getUserID() != NULL);
		
		$result = SQL("SELECT * FROM SESSION");
		$secondTest = (count($result) == 0);

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($firstTest && $secondTest);
	}
	
	
	
	public function testLogOutFromAllDevices()
	{
		$obj1 = UserManagement::createUser("owasp1", "owasp", "rac130@pitt.edu"); //create a new user.
		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj3 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		
		//set session variables to imitate real cookies.
		$randomValue = randstr(32);
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array($randomValue, time(), time(), $obj3->getUserID()));
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array(randstr(32), time(), time(), $obj3->getUserID()));
		SQL("INSERT INTO `SESSION` (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array(randstr(32), time(), time(), $obj3->getUserID()));
		$_COOKIE['sessionid'] = $randomValue;
		
		UserManagement::logOutFromAllDevices($obj1->getUserID());
		
		$result = SQL("SELECT * FROM SESSION");
		$Test = (count($result) == 0);

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($Test);
	}
}