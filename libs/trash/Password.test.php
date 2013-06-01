<?php
namespace phpsec;

require_once "../../../libs/db/adapter/pdo_mysql.php";
require_once '../../../libs/security/Password.class.php';
require_once '../../../libs/core/Rand.class.php';

class SaltTest extends \PHPUnit_Framework_TestCase
{
	private $obj = null;
	private $conn = null;
	
	private $_username = "";
	private $_rawPassword = "";
	private $_hashAlgo = "";

	private $_hashedPassword = "";
	
	private static $_staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	private $_dynamicSalt = "";
	
	public function setUp()
	{
		Time::$realTime = true;

		try
		{
			$this->conn = new \phpsec\Database_pdo_mysql ('OWASP', 'root', 'testing');
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
		$this->_username = "rash";
		$this->_rawPassword = "testing";
		$this->_hashAlgo = "tiger192,4";
		$this->_dynamicSalt = Rand::generateRandom(64);
		
		$this->obj = new Password($this->conn, $this->_username, $this->_rawPassword, $this->_dynamicSalt, $this->_hashAlgo);
	}
	
	public function testGetUsername()
	{
		$this->assertTrue($this->obj->getUsername() == $this->_username);
	}
	
	public function testHashPassword()
	{
		$this->_hashedPassword = $this->obj->hashPassword("user", "password");
		
		$this->assertTrue(strlen($this->_hashedPassword) > 1);
	}
	
	public function testGetHashedPassword()
	{
		try
		{
			$this->assertTrue(strlen($this->obj->getHashedPassword()) > 1);
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
			$firstTest = $this->obj->validatePassword("testing");
			$secondTest = $this->obj->validatePassword("resting");
			
			$this->assertTrue($firstTest && !$secondTest);
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
//			$query = "SELECT HASH FROM PASSWORD WHERE USERID = ?";
//			$args = array("{$this->_username}");
//			$result = $this->conn -> SQL($query, $args);
//			print_r($result);
			
			$firstTest = $this->obj->resetPassword("resting", "owaspphp");
			$secondTest = $this->obj->resetPassword("testing", "owaspphp");
			
//			$query = "SELECT HASH FROM PASSWORD WHERE USERID = ?";
//			$args = array("{$this->_username}");
//			$result = $this->conn -> SQL($query, $args);
//			print_r($result);echo strlen($result[0]['HASH']);
			
			$this->assertTrue(!$firstTest && $secondTest);
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
			$query = "DELETE FROM PASSWORD WHERE USERID = ?";
			$args = array("{$this->_username}");
			$count = $this->conn -> SQL($query, $args);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
		
		$this->_username = null;
		$this->_rawPassword = null;
		$this->_hashAlgo = null;
		$this->_hashedPassword = null;
		
		$this->obj = null;
	}
}

?>