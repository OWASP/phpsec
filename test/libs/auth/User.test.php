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
		$this->obj = User::newUserObject($this->_handler, "rash", "testing");
	}
	
	//--------------------------------------------------------------------------------------------------------------------------------------
	//for class User.
	
	public function testSetOptionalFields()
	{
		$this->obj->setOptionalFields("rahul300chaudhary400@gmail.com", "Rahul", "Chaudhary");
		
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
	
	public function testGetAccountCreationDate()
	{
		Time::$realTime = TRUE;
		
		$currentTime = Time::time();
		$creationTime = $this->obj->getAccountCreationDate();
		
		$this->assertTrue( ($currentTime >= $creationTime) && (strlen( (string)$creationTime ) == 10) );
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
			
			$query = "SELECT `HASH`, `DYNAMIC_SALT`, `ALGO` FROM USER WHERE USERID = ?";
			$args = array($this->obj->getUserID());
			$result = $this->_handler->SQL($query, $args);
			
			$firstAttempt = BasicPasswordManagement::validatePassword($newPassword, $result[0]['HASH'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']);
			
			$this->obj->resetPassword($oldPassword, $newPassword);
			
			$query = "SELECT `HASH`, `DYNAMIC_SALT`, `ALGO` FROM USER WHERE USERID = ?";
			$args = array($this->obj->getUserID());
			$result = $this->_handler->SQL($query, $args);
			
			$secondAttempt = BasicPasswordManagement::validatePassword($newPassword, $result[0]['HASH'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']);
			
			$this->assertTrue(!$firstAttempt && $secondAttempt);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testSetStaticSalt()
	{
		try
		{
			$this->obj2 = User::newUserObject($this->_handler, "rahul", "owasp pass", hash("sha512", Rand::generateRandom(64)) );
			$this->obj2 = null;
			$this->obj2 = User::existingUserObject($this->_handler, "rahul", "owasp pass");
			
			$firstTest = BasicPasswordManagement::validatePassword("owasp pass", $this->obj2->getHashedPassword(), $this->obj2->getDynamiSalt(), BasicPasswordManagement::$hashAlgo);
			$secondTest = BasicPasswordManagement::validatePassword("other password", $this->obj2->getHashedPassword(), $this->obj2->getDynamiSalt(), BasicPasswordManagement::$hashAlgo);
			
			$this->obj2->deleteUser();
			
			$this->assertTrue($firstTest && !$secondTest);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testCheckIfPasswordExpired()
	{
		try
		{
			$currentTime = Time::time();
			
			User::setPasswordExpiryTime(1000);
			Time::$realTime = false;
			Time::setTime($currentTime + 5000);
			
			$this->assertTrue($this->obj->checkIfPasswordExpired());
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------------
	//for class BasicPasswordManagement
	
	public function testGetStaticSalt()
	{
		$this->assertTrue(strlen(BasicPasswordManagement::getStaticSalt()) > 1);
	}
	
	public function testEntropy()
	{
		$this->assertTrue(BasicPasswordManagement::Entropy( "OWASP PHP") > 1);
	}
	
	public function testHasOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasOrderedCharacters( "abcd", 3 ) && BasicPasswordManagement::hasOrderedCharacters( "dcba", 3 ) && !BasicPasswordManagement::hasOrderedCharacters( "abed", 3 ));
	}
	
	public function testHasKeyboardOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasKeyboardOrderedCharacters( "qwert", 3 ) && BasicPasswordManagement::hasKeyboardOrderedCharacters( "trewq", 3 ) && !BasicPasswordManagement::hasKeyboardOrderedCharacters( "trwwQz", 3 ));
	}
	
	public function testIsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::isPhoneNumber( "4125199634") && BasicPasswordManagement::isPhoneNumber("+14125199634") && !BasicPasswordManagement::isPhoneNumber("412-519-9634"));
	}
	
	public function testContainsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::containsPhoneNumber("rash4125199634") && BasicPasswordManagement::containsPhoneNumber("+14125199634rahul") && !BasicPasswordManagement::isPhoneNumber("412-519-9634"));
	}
	
	public function testIsDate()
	{
		$this->assertTrue(BasicPasswordManagement::isDate("23-May 2012") && BasicPasswordManagement::isDate("may/21-1990") && BasicPasswordManagement::isDate("2021 FeB.13") && !BasicPasswordManagement::isPhoneNumber("rash21-May-rash"));
	}
	
	public function testContainsDate()
	{
		$this->assertTrue(BasicPasswordManagement::containsDate("ra23-May 2012aa") && BasicPasswordManagement::containsDate("may/21-90aqw") && BasicPasswordManagement::containsDate("qw21 FeB.13") && !BasicPasswordManagement::isPhoneNumber("23/01//13"));
	}
	
	public function testContainsDoubledWords()
	{
		$this->assertTrue(BasicPasswordManagement::containDoubledWords( "dogdog") && !BasicPasswordManagement::containDoubledWords( "dogdogs"));
	}
	
	public function testContainsString()
	{
		$this->assertTrue(BasicPasswordManagement::containsString( "this is a sTring", "sTRinG") && !BasicPasswordManagement::containsString( "my string is this", "rash"));
	}
	
	public function testStrength()
	{
		$this->assertTrue((BasicPasswordManagement::strength("ABCDEFGH") < 0.1) && (BasicPasswordManagement::strength("Tes\$ing") > 0.5) && (BasicPasswordManagement::strength("Tes\$ingTes\$ing") < 0.5));
	}
	
	public function testGenerate()
	{
		$this->assertTrue((strlen(BasicPasswordManagement::generate(0.1)) == 4) && (strlen(BasicPasswordManagement::generate(0.4)) == 8) && (strlen(BasicPasswordManagement::generate(0.8)) == 16));
	}
	
	
	
	//Global tear-down function.
	
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