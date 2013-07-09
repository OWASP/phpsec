<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/db/dbmanager.php";
require_once ("../../../libs/auth/usermanagement.php");



class UserManagementTest extends \PHPUnit_Framework_TestCase
{
	
	
	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		try
		{
			DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP','root','testing'));	//create a DB connection.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
	}
	
	
	
	
	/**
	 * Function to test createUser, deleteUser and userExists functionalities.
	 */
	public function testUser_Create_Delete_Exists()
	{
		UserManagement::createUser("owasp1", "owasp");	//create a user.
		
		$firstTest = UserManagement::userExists("owasp1");	//test user that exists.
		$secondTest = UserManagement::userExists("owasp2");	//test user that does NOT exists.
		
		UserManagement::deleteUser("owasp1", "owasp");	//delete the created user.
		
		$this->assertTrue($firstTest && !$secondTest);
	}
	
	
	
	/**
	 * Function to test the total number users present in the DB.
	 */
	public function testUserCount()
	{
		//create two users.
		UserManagement::createUser("owasp1", "owasp");
		UserManagement::createUser("owasp2", "owasp");
		
		$count = UserManagement::userCount();
		
		//delete the newly created users.
		UserManagement::deleteUser("owasp1", "owasp");
		UserManagement::deleteUser("owasp2", "owasp");
		
		//total number of users must be 2.
		$this->assertTrue($count == 2);
	}
	
	
	
	/**
	 * Function to test if the user provided credentials are correct or not.
	 */
	public function testValidateUserCredentials()
	{
		UserManagement::createUser("owasp1", "owasp");		//create a user.
		
		$firstTest = UserManagement::validateUserCredentials("owasp1", "owasp");			//test credentials with correct data.
		$secondTest = UserManagement::validateUserCredentials("owasp1", "wrongPassword");	//test credentials with incorrect password.
		$thirdTest = UserManagement::validateUserCredentials("wrongUsername", "owasp");		//test credentials with incorrect username.
		
		UserManagement::deleteUser("owasp1", "owasp");		//delete the newly created user.
		
		$this->assertTrue($firstTest && !$secondTest && !$thirdTest);
	}
}