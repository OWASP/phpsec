<?php
namespace phpsec;

require_once '../../../libs/security/Salt.class.php';

class SaltTest extends \PHPUnit_Framework_TestCase
{
	public function testMake($username = "rash", $password = "testing")
	{
		$saltedString = Salt::make($username, $password);
		
		$len = strlen($saltedString) - (strlen($username) + strlen($password));
		$this->assertTrue(($len == 256) && (substr($saltedString, 0, strlen($username)) == $username));
	}
}

?>