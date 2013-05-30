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
	
	public function setUp()
	{
		$this->_username = "rash";
		$this->_rawPassword = "testing";
		$this->_hashAlgo = "tiger192,4";
		
		$this->obj = new Password($this->_username, $this->_rawPassword);
		Password::$hashAlgo = $this->_hashAlgo;
	}
	
	public function testGetUsername()
	{
		$this->assertTrue($this->obj->getUsername() == $this->_username);
	}
	
	public function testGetHashAlgo()
	{
		$this->assertTrue(Password::$hashAlgo == $this->_hashAlgo);
	}
	
	public function testHashPassword()
	{
		$this->_hashedPassword = $this->obj->hashPassword();
		
		$this->assertTrue(strlen($this->_hashedPassword) > 1);
	}
	
	public function testGetHashedPassword()
	{
		try
		{
			$this->_hashedPassword = $this->obj->hashPassword();
			$this->assertTrue($this->obj->getHashedPassword() == $this->_hashedPassword);
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