<?php
namespace phpsec;



/**
 * Required Files.
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/auth/xuser.php";



class XUserTest extends \PHPUnit_Framework_TestCase
{



	/**
	 * The object of class User
	 * @var \phpsec\User
	 */
	private $obj;



	/**
	 * The object of class XUser
	 * @var \phpsec\XUser
	 */
	private $xobj;



	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose a hashing algo.
		User::newUserObject("rash", 'testing', "rac130@pitt.edu"); //create a new user.
		User::activateAccount("rash");	//activate the user account
		$this->obj = User::existingUserObject("rash", "testing");	//get the user object
		$this->xobj = new XUser($this->obj);		//get the XUser object
	}



	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		$this->xobj->deleteXUser();
		$this->obj->deleteUser();
	}



	/**
	 * Function to test if names are stored correctly
	 */
	public function testSetName()
	{
		$fname = "Rahul";
		$lname = "Chaudhary";
		$this->xobj->setName($fname, $lname);

		$result = SQL("SELECT `FIRST_NAME`, `LAST_NAME` FROM XUSER WHERE USERID = ?", array($this->obj->getUserID()));

		$this->assertTrue(($result[0]['FIRST_NAME'] === $fname) && ($result[0]['LAST_NAME'] === $lname));
	}



	/**
	 * Function to test if secondary email is stored correctly.
	 */
	public function testSetSecondaryEmail()
	{
		$semail = "rac130@pitt.edu";
		$this->xobj->setSecondaryEmail($semail);

		$result = SQL("SELECT `S_EMAIL` FROM XUSER WHERE USERID = ?", array($this->obj->getUserID()));

		$this->assertTrue(($result[0]['S_EMAIL'] === $semail));
	}



	/**
	 * Function to test if DOB is stored correctly.
	 */
	public function testSetDOB()
	{
		$dob = time() - 10000;
		$this->xobj->setDOB($dob);

		$result = SQL("SELECT `DOB` FROM XUSER WHERE USERID = ?", array($this->obj->getUserID()));
		$this->assertTrue(($result[0]['DOB'] == $dob));
	}



	/**
	 * Function to test if the user's age satisfies the minimum age criteria
	 */
	public function testAgeCheck()
	{
		$dob = time() - 10000;
		$this->xobj->setDOB($dob);

		$this->assertTrue(! $this->xobj->ageCheck());
	}
}
