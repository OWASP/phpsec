<?php
namespace phpsec;


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
		$userObj = UserManagement::createUser("owasp1", "owasp"); //create a user.

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
		$userObj1 = UserManagement::createUser("owasp1", "owasp");
		$userObj2 = UserManagement::createUser("owasp2", "owasp");

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
		$obj1 = UserManagement::createUser("owasp1", "owasp"); //create a new user.
		$obj2 = UserManagement::forceLogIn("owasp1"); //try to force-login this user.

		$test = $obj1->getUserID() == $obj2->getUserID(); //check if both of these objects are same.

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue($test);
	}


	/**
	 * Function to check deviceLoggedIn, logOutFromAllDevices function.
	 */
	public function testIsLoggedIn()
	{
		$obj1 = UserManagement::createUser("owasp1", "owasp"); //create a new user.
		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj3 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj4 = UserManagement::createUser("owasp2", "owasp"); //create a new user.

		$firstTest = UserManagement::devicesLoggedIn("owasp1"); //check how many deviced are logged-in in name of this user.

		UserManagement::logOutFromAllDevices("owasp1"); //log-out from all devices under this user's name.

		$secondTest = UserManagement::devicesLoggedIn("owasp1"); //check how many deviced are logged-in in name of this user.

		$thirdTest = UserManagement::devicesLoggedIn("owasp2"); //check how many deviced are logged-in in name of this user.

		UserManagement::deleteUser("owasp1"); //delete the newly created users.
		UserManagement::deleteUser("owasp2"); //delete the newly created users.

		$this->assertTrue(($firstTest == 3) && ($secondTest == 0) && ($thirdTest == 1));
	}


	/**
	 * Function to check logOut function.
	 */
	public function testLogOut()
	{
		$obj1 = UserManagement::createUser("owasp1", "owasp"); //create a new user.
		$obj2 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.
		$obj3 = UserManagement::logIn("owasp1", "owasp"); //log in the same user from different device.

		$firstTest = UserManagement::devicesLoggedIn("owasp1"); //check how many deviced are logged-in in name of this user.

		UserManagement::logOut($obj1); //log-out the user from 1 device.
		UserManagement::logOut($obj2); //log-out the user from 1 device.

		$secondTest = UserManagement::devicesLoggedIn("owasp1"); //check how many deviced are logged-in in name of this user.

		UserManagement::deleteUser("owasp1"); //delete the newly created users.

		$this->assertTrue(($firstTest == 3) && ($secondTest == 1));
	}
}