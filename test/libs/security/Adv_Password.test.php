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
	
	public function testCheckIfTempPassExpired()
	{
		try
		{
			$query = "UPDATE PASSWORD SET TEMP_TIME = ? WHERE USERID = ?";
			$args = array(Time::time(), $this->_userID->getUserID());
			$count = $this->_handler->SQL($query, $args);
			
			$firstTest = $this->obj->checkIfTempPassExpired();

			Time::$realTime = false;
			Time::setTime(1390706853);

			$secondTest = $this->obj->checkIfTempPassExpired();
			
			$query = "UPDATE PASSWORD SET TEMP_TIME = ? WHERE USERID = ?";
			$args = array(0, $this->_userID->getUserID());
			$count = $this->_handler->SQL($query, $args);

			$this->assertTrue(!$firstTest && $secondTest);
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	public function testTempPassword()
	{
		try
		{
			$currentTime = Time::time();
			
			AdvancedPasswordManagement::setTempPassExpiryTime(900);
			
			$this->obj->tempPassword();
			
			$query = "SELECT TEMP_PASS FROM PASSWORD WHERE USERID = ?";
			$args = array($this->_userID->getUserID());
			$result = $this->_handler -> SQL($query, $args);
			
			Time::$realTime = false;
			
			//firstTest
			Time::setTime($currentTime + 500);
			$firstTest = $this->obj->tempPassword("qwert");
			
			//secondTesSt
			Time::setTime($currentTime + 500);
			$secondTest = $this->obj->tempPassword($result[0]['TEMP_PASS']);
			
			$this->assertTrue(!$firstTest && $secondTest);
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	public function tearDown()
	{
		$this->_handler = null;
		$this->_userID = null;
		$this->obj = null;
		
		Time::$realTime = true;
	}
}

?>