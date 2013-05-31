<?php
namespace phpsec;

require_once '../../../libs/security/Password.class.php';

class SaltTest extends \PHPUnit_Framework_TestCase
{
	private $obj = null;
	
	private $_username = "";
	private $_rawPassword = "";
	private $_hashAlgo = "";

	private $_hashedPassword = "";
	
	private static $_staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	private $_dynamicSalt = "";
	
	public function setUp()
	{
		$this->_username = "rash";
		$this->_rawPassword = "testing";
		$this->_hashAlgo = "tiger192,4";
		$this->_dynamicSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb";
		
		$this->obj = new Password($this->_username, $this->_rawPassword, $this->_dynamicSalt, $this->_hashAlgo);
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
	
	public function tearDown()
	{
		$this->_username = null;
		$this->_rawPassword = null;
		$this->_hashAlgo = null;
		$this->_hashedPassword = null;
		
		$this->obj = null;
	}
}

?>