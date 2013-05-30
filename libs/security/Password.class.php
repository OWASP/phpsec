<?php
namespace phpsec;

require_once __DIR__ . '/Salt.class.php';
require_once __DIR__ . '/../core/Exception.class.php';

class Password
{
	private $_username = "";
	private $_rawPassword = "";
	
	private $_hashedPassword = "";
	
	public static $hashAlgo = "";
	
	public function __construct($user, $pass)
	{
		$this->_username = $user;
		$this->_rawPassword = $pass;
	}
	
	public function getUsername()
	{
		return $this->_username;
	}
	
	public function getHashedPassword()
	{
		if ($this->_hashedPassword == "")
			throw new InvalidHashException("<BR>WARNING: This hash seems invalid.<BR>");
		else
			return $this->_hashedPassword;
	}
	
	public function hashPassword()
	{
		$this->_hashedPassword = hash(Password::$hashAlgo, Salt::make($this->_username, $this->_rawPassword));
		
		return $this->_hashedPassword;
	}
}

?>