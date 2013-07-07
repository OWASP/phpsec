<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once "../../../libs/db/dbmanager.php";
require_once '../../../libs/core/random.php';
require_once '../../../libs/auth/user.php';


class UserTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		Time::$realTime = true;

		try
		{
			DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP','root','testing'));
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
		BasicPasswordManagement::$hashAlgo = "haval256,5";	//choose a hashing algo.
		$this->obj = User::newUserObject("rash", "testing");	//create a new user.
	}
	
	

	//--------------------------------------------------------------------------------------------------------------------------------------
	//for class User.
	
	
	
	/**
	 * To check the account creation date.
	 */
	public function testGetAccountCreationDate()
	{
		Time::$realTime = TRUE;
		
		$currentTime = Time::time();	//get current time.
		$creationTime = $this->obj->getAccountCreationDate();
		
		//the current time must be greater than the time it was created.
		$this->assertTrue( ($currentTime >= $creationTime) && (strlen( (string)$creationTime ) == 10) );
	}
	
	
	/**
	 * To check if the passwords are validated on providing passwords.
	 */
	public function testValidatePassword()
	{
		try
		{
			//provide correct password
			$firstTest = $this->obj->verifyPassword("testing");
			//provide wrong password
			$secondTest = $this->obj->verifyPassword("resting");
			
			//first test would succeed and second won't.
			$this->assertTrue($firstTest && !$secondTest);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if we get object of an existing user.
	 */
	public function testExistingUser()
	{
		try
		{
			$this->obj = null;	//destroy the object to current user.
			$this->obj = User::existingUserObject("rash", "testing");	//get the object of this user again via this method.
			
			//try to run validate password function with this new object.
			$test = $this->obj->verifyPassword("testing");
			$this->assertTrue($test);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * Function to check if passwords are reset.
	 */
	public function testResetPassword()
	{
		try
		{
			$newPassword = "resting";
			$oldPassword = "testing";
			
			//try to reset password by providing wrong password.
			$firstAttempt = $this->obj->verifyPassword($newPassword);
			
			$this->obj->resetPassword($oldPassword, $newPassword);
			
			//try to reset password by providing correct password.
			$secondAttempt = $this->obj->verifyPassword($newPassword);
			
			$this->assertTrue(!$firstAttempt && $secondAttempt);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if we can set static salts.
	 */
	public function testSetStaticSalt()
	{
		try
		{
			//create a new user with user provided static salt. This will set the static salt to this provided salt.
			$this->obj2 = User::newUserObject("rahul", "owasp pass", hash("sha512", Rand::generateRandom(64)) );
			//delete this object.
			$this->obj2 = null;
			//revive this user's object again.
			$this->obj2 = User::existingUserObject("rahul", "owasp pass");
			
			//try to validate password by giving correct password. Note that the static salt has already been set.
			$firstTest = $this->obj2->verifyPassword("owasp pass");
			//try to validate password by giving wrong password. Note that the static salt has already been set.
			$secondTest = $this->obj2->verifyPassword("other password");
			
			$this->obj2->deleteUser();
			
			$this->assertTrue($firstTest && !$secondTest);
		}
		catch(\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if password has expired or not.
	 */
	public function testIsPasswordExpired()
	{
		try
		{
			$currentTime = Time::time();
			
			User::$passwordExpiryTime = 1000;	//set the password expiry time to 1000.
			Time::$realTime = false;
			Time::setTime($currentTime + 5000);	//set a new false time that is bound to exceed the expiry limit.
			
			$this->assertTrue($this->obj->isPasswordExpired());
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------------
	//for class BasicPasswordManagement
	
	
	/**
	 * To check if we can retieve the static salt.
	 */
	public function testGetStaticSalt()
	{
		$this->assertTrue(strlen(BasicPasswordManagement::getStaticSalt()) > 1);
	}
	
	
	/**
	 * To check if we can get the entropy. This string will produce an entropy greater than 1.
	 */
	public function testEntropy()
	{
		$this->assertTrue(BasicPasswordManagement::Entropy( "OWASP PHP") > 1);
	}
	
	
	/**
	 * To check if a string as ordered characters. (3 cases are checked).
	 */
	public function testHasOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasOrderedCharacters( "abcd", 3 ) && BasicPasswordManagement::hasOrderedCharacters( "dcba", 3 ) && !BasicPasswordManagement::hasOrderedCharacters( "abed", 3 ));
	}
	
	
	/**
	 * To check if a string as keyboard ordered characters. (3 cases are checked).
	 */
	public function testHasKeyboardOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasKeyboardOrderedCharacters( "qwert", 3 ) && BasicPasswordManagement::hasKeyboardOrderedCharacters( "trewq", 3 ) && !BasicPasswordManagement::hasKeyboardOrderedCharacters( "trwwQz", 3 ));
	}
	
	
	/**
	 * To check if the string is a phone number (3 cases are checked)
	 */
	public function testIsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::isPhoneNumber( "4125199634") && BasicPasswordManagement::isPhoneNumber("+14125199634") && !BasicPasswordManagement::isPhoneNumber("412-519-9634"));
	}
	
	
	/**
	 * To check if the string contains a phone-pattern (3 cases are checked)
	 */
	public function testContainsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::containsPhoneNumber("rash4125199634") && BasicPasswordManagement::containsPhoneNumber("+14125199634rahul") && !BasicPasswordManagement::isPhoneNumber("412-519-9634"));
	}
	
	
	/**
	 * To check if the string is a date. (4 cases are checked)
	 */
	public function testIsDate()
	{
		$this->assertTrue(BasicPasswordManagement::isDate("23-May 2012") && BasicPasswordManagement::isDate("may/21-1990") && BasicPasswordManagement::isDate("2021 FeB.13") && !BasicPasswordManagement::isPhoneNumber("rash21-May-rash"));
	}
	
	
	/**
	 * To check if the string contains a date. (4 cases are checked)
	 */
	public function testContainsDate()
	{
		$this->assertTrue(BasicPasswordManagement::containsDate("ra23-May 2012aa") && BasicPasswordManagement::containsDate("may/21-90aqw") && BasicPasswordManagement::containsDate("qw21 FeB.13") && !BasicPasswordManagement::isPhoneNumber("23/01//13"));
	}
	
	
	/**
	 * To check if the string contains double words. (2 cases are checked)
	 */
	public function testContainsDoubledWords()
	{
		$this->assertTrue(BasicPasswordManagement::containDoubledWords( "dogdog") && !BasicPasswordManagement::containDoubledWords( "dogdogs"));
	}
	
	
	/**
	 * To check if a string contains another string. (2 cases are checked)
	 */
	public function testContainsString()
	{
		$this->assertTrue(BasicPasswordManagement::containsString( "this is a sTring", "sTRinG") && !BasicPasswordManagement::containsString( "my string is this", "rash"));
	}
	
	
	/**
	 * To check if we can get the strength of a string. (3 cases are checked)
	 */
	public function testStrength()
	{
		$this->assertTrue((BasicPasswordManagement::strength("ABCDEFGH") < 0.1) && (BasicPasswordManagement::strength("Tes\$ing") > 0.5) && (BasicPasswordManagement::strength("Tes\$ingTes\$ing") < 0.5));
	}
	
	
	/**
	 * To check if we can generate a random string of given strength. (3 cases are checked)
	 */
	public function testGenerate()
	{
		$this->assertTrue((strlen(BasicPasswordManagement::generate(0.1)) == 4) && (strlen(BasicPasswordManagement::generate(0.4)) == 8) && (strlen(BasicPasswordManagement::generate(0.8)) == 16));
	}
	
	
	
	//Global tear-down function.
	
	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
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
	}
}

?>
