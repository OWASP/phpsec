<?php
namespace phpsec;

require_once "../../../libs/db/adapter/pdo_mysql.php";
require_once '../../../libs/core/Rand.class.php';
require_once '../../../libs/auth/User.class.php';


class UserTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		Time::$realTime = true;

		try
		{
			$this->_handler = new \phpsec\Database_pdo_mysql ('OWASP', 'root', 'testing');
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		BasicPasswordManagement::$hashAlgo = "haval256,5";
		$this->obj = User::newUserObject($this->_handler, "rash", "testing", "rahul300chaudhary400@gmail.com");
	}
	
	public function testSetOptionalFields()
	{
		$this->obj->setOptionalFields("Rahul", "Chaudhary");
		
		try
		{
			$query = "SELECT FIRST_NAME FROM USER WHERE USERID = ?";
			$args = array("{$this->obj->getUserID()}");
			$result = $this->_handler -> SQL($query, $args);
			
			$this->assertTrue($result[0]['FIRST_NAME'] == "Rahul");
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testHashPassword()
	{
		$hash = BasicPasswordManagement::hashPassword("password", Rand::generateRandom(64), "sha512");
		
		$this->assertTrue(strlen($hash) == 128);
	}
	
	public function testGetHashedPassword()
	{
		try
		{
			$hash = $this->obj->getHashedPassword();
		
			$this->assertTrue(strlen($hash) > 1);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testValidatePassword()
	{
		try
		{
			$firstTest = BasicPasswordManagement::validatePassword("testing", $this->obj->getHashedPassword(), $this->obj->getDynamiSalt(), BasicPasswordManagement::$hashAlgo);
			$secondTest = BasicPasswordManagement::validatePassword("resting", $this->obj->getHashedPassword(), $this->obj->getDynamiSalt(), BasicPasswordManagement::$hashAlgo);
			
			$this->assertTrue($firstTest && !$secondTest);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testExistingUser()
	{
		try
		{
			$this->obj = null;
			$this->obj = User::existingUserObject($this->_handler, "rash", "testing");
			
			$test = BasicPasswordManagement::validatePassword("testing", $this->obj->getHashedPassword(), $this->obj->getDynamiSalt(), BasicPasswordManagement::$hashAlgo);
			$this->assertTrue($test);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testResetPassword()
	{
		try
		{
			$newPassword = "resting";
			$oldPassword = "testing";
			
			$oldHash = $this->obj->getHashedPassword();
			
			$this->obj->resetPassword($oldPassword, $newPassword);
			
			$newHash = $this->obj->getHashedPassword();
			
			$this->assertTrue($oldHash != $newHash);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function tearDown()
	{
		try
		{
			$this->obj->deleteUser();
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
		
		$this->obj = null;
		$this->_handler = null;
	}
}

?>