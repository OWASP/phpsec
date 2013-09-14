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
	 * @var User
	 */
	private $obj;
	private $xobj;

	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		BasicPasswordManagement::$hashAlgo = "haval256,5"; //choose a hashing algo.
		$this->obj = User::newUserObject("rash", 'testing', "rac130@pitt.edu"); //create a new user.
		$this->xobj = new XUser($this->obj);
	}

	/**
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		$this->xobj->deleteXUser();
		$this->obj->deleteUser();
	}

	public function testSetName()
	{
		$fname = "Rahul";
		$lname = "Chaudhary";
		$this->xobj->setName($fname, $lname);
		
		$result = SQL("SELECT `FIRST_NAME`, `LAST_NAME` FROM XUSER WHERE USERID = ?", array($this->obj->getUserID()));
		
		$this->assertTrue(($result[0]['FIRST_NAME'] === $fname) && ($result[0]['LAST_NAME'] === $lname));
	}
	
	public function testSetSecondaryEmail()
	{
		$semail = "rac130@pitt.edu";
		$this->xobj->setSecondaryEmail($semail);
		
		$result = SQL("SELECT `S_EMAIL` FROM XUSER WHERE USERID = ?", array($this->obj->getUserID()));
		
		$this->assertTrue(($result[0]['S_EMAIL'] === $semail));
	}
	
	public function testSetDOB()
	{
		$dob = time() - 10000;
		$this->xobj->setDOB($dob);
		
		$result = SQL("SELECT `DOB` FROM XUSER WHERE USERID = ?", array($this->obj->getUserID()));
		$this->assertTrue(($result[0]['DOB'] == $dob));
	}
	
	public function testAgeCheck()
	{
		$dob = time() - 10000;
		$this->xobj->setDOB($dob);
		
		$this->assertTrue(! $this->xobj->ageCheck());
	}
	
	public function testSecurityAnswers()
	{
		$ans1 = "abc";
		$ans2 = "xyz";
		$this->xobj->setSecurityAnswers($ans1, $ans2);
		
		$test1 = $this->xobj->checkSecurityAnswer1($this->obj->getUserID(), $ans1);
		$test2 = $this->xobj->checkSecurityAnswer1($this->obj->getUserID(), $ans2);
		$test3 = $this->xobj->checkSecurityAnswer2($this->obj->getUserID(), $ans2);
		$test4 = $this->xobj->checkSecurityAnswer2($this->obj->getUserID(), $ans1);
		
		$this->assertTrue($test1 && !$test2 && $test3 && !$test4);
	}
}
