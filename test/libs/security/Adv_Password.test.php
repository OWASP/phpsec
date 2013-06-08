<?php
namespace phpsec;

require_once "../../../libs/db/adapter/pdo_mysql.php";
require_once '../../../libs/core/Rand.class.php';
require_once '../../../libs/core/Time.class.php';
require_once '../../../libs/auth/User.class.php';
require_once '../../../libs/security/Adv_Password.class.php';

class AdvPasswordTest extends \PHPUnit_Framework_TestCase
{
	private $_handler = "";
	private $_userID = "";
	private $obj = "";
	
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
		
		try
		{
			BasicPasswordManagement::$hashAlgo = "haval256,5";
			$this->_userID = User::newUserObject($this->_handler, Rand::generateRandom(10), "testing");
			$this->obj = new AdvancedPasswordManagement($this->_handler, $this->_userID);
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	public function testSetTempPassExpiryTime()
	{
		$time = 1800;
		
		AdvancedPasswordManagement::setTempPassExpiryTime($time);
		
		$this->assertTrue(AdvancedPasswordManagement::getTempPassExpiryTime() == $time);
	}
	
	public function testCheckIfTimeExpired()
	{
		$currentTime = Time::time();
		
		$firstTest = AdvancedPasswordManagement::checkIfTimeExpired( $currentTime);
		
		Time::$realTime = false;
		Time::setTime(1370706853);
		
		$secondTest = AdvancedPasswordManagement::checkIfTimeExpired( $currentTime);
		
		Time::$realTime = true;
		
		$this->assertTrue(!$firstTest && $secondTest);
	}
	
	public function testForgotPassword()
	{
		try
		{
			$currentTime = Time::time();
			
			AdvancedPasswordManagement::setTempPassExpiryTime(900);
			
			$this->obj->forgotPassword();
			
			$query = "SELECT TEMP_PASS FROM PASSWORD WHERE USERID = ?";
			$args = array($this->_userID->getUserID());
			$result = $this->_handler -> SQL($query, $args);
			
			Time::$realTime = false;
			
			//firstTest
			Time::setTime($currentTime + 500);
			$firstTest = $this->obj->forgotPassword("qwert");
			
			//secondTest
			Time::setTime($currentTime + 500);
			$secondTest = $this->obj->forgotPassword($result[0]['TEMP_PASS']);
			
			
			Time::$realTime = true;
			$this->assertTrue(!$firstTest && $secondTest);
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
}

?>