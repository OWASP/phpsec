<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/core/random.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/core/time.php";
require_once(__DIR__ . "/../../../libs/crypto/confidentialstring.php");


class UserTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var User
	 */
	private $obj;

	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose a hashing algo.
		$this->obj = User::newUserObject("rash", 'testing'); //create a new user.
	}

	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		$this->obj->deleteUser();
	}

	/**
	 * To check the account creation date.
	 */
	public function testGetAccountCreationDate()
	{
		$currentTime = time("SYS"); //get current time.
		$creationTime = $this->obj->getAccountCreationDate();


		//the current time must be greater than the time it was created.
		$this->assertTrue(($currentTime >= $creationTime) && (strlen((string)$creationTime) == 10));
	}


	/**
	 * To check if the passwords are validated on providing passwords.
	 */
	public function testValidatePassword()
	{
		//provide correct password
		$this->assertTrue($this->obj->verifyPassword('testing'));
		//provide wrong password
		$this->assertFalse($this->obj->verifyPassword("resting"));
	}


	/**
	 * To check if we get object of an existing user.
	 */
	public function testExistingUser()
	{
		$this->obj = User::existingUserObject("rash", 'testing'); //get the object of this user again via this method.

		//try to run validate password function with this new object.
		$this->assertTrue($this->obj->verifyPassword('testing'));
	}


	/**
	 * Function to check if passwords are reset.
	 */
	public function testResetPassword()
	{
		$newPassword = "resting";
		$oldPassword = "testing";

		//try to reset password by providing wrong password.
		$this->assertFalse($this->obj->verifyPassword($newPassword));

		$this->obj->resetPassword($oldPassword, $newPassword);

		//try to reset password by providing correct password.
		$this->assertTrue($this->obj->verifyPassword($newPassword));
	}


	/**
	 * To check if we can set static salts.
	 */
	public function testSetStaticSalt()
	{
		//create a new user with user provided static salt. This will set the static salt to this provided salt.
		$obj2 = User::newUserObject("rahul", "owasp pass", hash("sha512", randstr(64)));
		//delete this object.
		$obj2 = null;
		//revive this user's object again.
		$obj2 = User::existingUserObject("rahul", "owasp pass");

		//try to validate password by giving correct password. Note that the static salt has already been set.
		$this->assertTrue($obj2->verifyPassword("owasp pass"));
		//try to validate password by giving wrong password. Note that the static salt has already been set.
		$this->assertFalse($obj2->verifyPassword("other password"));

		$obj2->deleteUser(); // this should move to teardown
	}


	/**
	 * To check if password has expired or not.
	 */
	public function testIsPasswordExpired()
	{
		$currentTime = time("SYS");

		User::$passwordExpiryTime = 1000; //set the password expiry time to 1000.
		time("SET", $currentTime + 5000); //set a new false time that is bound to exceed the expiry limit.

		$this->assertTrue($this->obj->isPasswordExpired());
	}


	/**
	 * To check if we can retrieve the static salt.
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
		$this->assertTrue(BasicPasswordManagement::Entropy("OWASP PHP") > 1);
	}


	/**
	 * To check if a string as ordered characters. (3 cases are checked).
	 */
	public function testHasOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasOrderedCharacters("abcd", 3));
		$this->assertTrue(BasicPasswordManagement::hasOrderedCharacters("dcba", 3));

		$this->assertFalse(BasicPasswordManagement::hasOrderedCharacters("abed", 3));
	}


	/**
	 * To check if a string as keyboard ordered characters. (3 cases are checked).
	 */
	public function testHasKeyboardOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasKeyboardOrderedCharacters("qwert", 3));
		$this->assertTrue(BasicPasswordManagement::hasKeyboardOrderedCharacters("trewq", 3));

		$this->assertFalse(BasicPasswordManagement::hasKeyboardOrderedCharacters("trwwQz", 3));
	}


	/**
	 * To check if the string is a phone number (3 cases are checked)
	 */
	public function testIsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::isPhoneNumber("4125199634"));
		$this->assertTrue(BasicPasswordManagement::isPhoneNumber("+14125199634"));

		$this->assertFalse(BasicPasswordManagement::isPhoneNumber("412-519-9634"));
	}


	/**
	 * To check if the string contains a phone-pattern (3 cases are checked)
	 */
	public function testContainsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::containsPhoneNumber("rash4125199634"));
		$this->assertTrue(BasicPasswordManagement::containsPhoneNumber("+14125199634rahul"));

		$this->assertFalse(BasicPasswordManagement::containsPhoneNumber("412-519-9634"));
	}


	public function provideValidDateStrings()
	{
		return array(
			"european english" => array("23-May 2012"),
			"american" => array("may/21-1990"),
			"iso caseinsensitive" => array("2021 FeB.13"),
		);
	}

	/**
	 * @dataProvider provideValidDateStrings
	 */
	public function testIsDateShouldReturnTrue($date)
	{
		$this->assertTrue(BasicPasswordManagement::isDate($date));
	}

	public function testIsDateShouldReturnFalse()
	{
		$this->assertFalse(!BasicPasswordManagement::isDate("rash21-May-rash"));
	}

	/**
	 * To check if the string contains a date. (4 cases are checked)
	 */
	public function testContainsDate()
	{
		$this->assertTrue(BasicPasswordManagement::containsDate("ra23-May 2012aa"));
		$this->assertTrue(BasicPasswordManagement::containsDate("may/21-90aqw"));
		$this->assertTrue(BasicPasswordManagement::containsDate("qw21 FeB.13"));
		$this->assertTrue(BasicPasswordManagement::containsDate("23/01//13"));
	}


	/**
	 * To check if the string contains double words. (2 cases are checked)
	 */
	public function testContainsDoubledWords()
	{
		$this->assertTrue(BasicPasswordManagement::containDoubledWords("dogdog"));
		$this->assertTrue(BasicPasswordManagement::containDoubledWords("dogdogs"));
	}


	/**
	 * To check if a string contains another string. (2 cases are checked)
	 */
	public function testContainsString()
	{
		$this->assertTrue(BasicPasswordManagement::containsString("this is a sTring", "sTRinG"));
		$this->assertFalse(BasicPasswordManagement::containsString("my string is this", "rash"));
	}


	/**
	 * To check if we can get the strength of a string. (3 cases are checked)
	 */
	public function testStrength()
	{
		$this->assertLessThan(0.1, BasicPasswordManagement::strength("ABCDEFGH"));
		$this->assertGreaterThan(0.5, BasicPasswordManagement::strength("Tes\$ing"));
		$this->assertLessThan(0.5, BasicPasswordManagement::strength("Tes\$ingTes\$ing"));
	}


	/**
	 * To check if we can generate a random string of given strength. (3 cases are checked)
	 */
	public function testGenerate()
	{
		$this->assertEquals(4, strlen(BasicPasswordManagement::generate(0.1)));
		$this->assertEquals(8, strlen(BasicPasswordManagement::generate(0.4)));
		$this->assertEquals(16, strlen(BasicPasswordManagement::generate(0.8)));
	}
}
