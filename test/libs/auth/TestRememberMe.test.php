<?php
namespace phpsec;

ini_set("display_errors", 1);

echo "<PRE>";

/**
 *					*****************IMPORTANT*****************************
 * 
 * This class is to test the "rememberMe" function in the file "/var/www/phpsec/libs/security/Adv_Password.php".
 * The reason is that I was having problems handling cookies with PHPUnit.
 * 
 * 
 * 
 * This file has 2 functions. TO test the code, do the following:
 * 
 * 1) Comment out the second function and the last line where this function is called.
 * 2) Then run the PHP file. Wait for 2-3 seconds, the run the file again. You should see the message that test passed or failed.
 * 
 * Now for the second function do the following:
 * 1) Uncomment the lines that you commented out above.
 * 2) Comment out the first function and the second last line where this function is called.
 * 2) Then run the PHP file. Wait for 3-4 seconds, the run the file again. You should see the message that test passed or failed.
 * 3) Check your browser cookies. You should not see a cookie of name "rash" under your TEST_SITE.
 * 
 * 
 * 
 */

require_once "../../../libs/db/adapter/pdo_mysql.php";
require_once '../../../libs/core/random.php';
require_once '../../../libs/core/time.php';
require_once '../../../libs/auth/user.php';
require_once '../../../libs/auth/adv_password.php';

class TestRememberMe
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
			$this->_userID = User::newUserObject($this->_handler, "rash", "testing");
			$this->obj = new AdvancedPasswordManagement($this->_handler, "rash", "testing");
		}
		catch (\Exception $e)
		{
			$this->_userID = User::existingUserObject($this->_handler, "rash", "testing");
			$this->obj = new AdvancedPasswordManagement($this->_handler, "rash", "testing");
		}
	}
	
	public function testRememberMe_1()
	{
		try
		{
			$this->obj->rememberMe(FALSE, FALSE);
			
			if (isset($_COOKIE["AUTHID"]))
			{
				if ($this->obj->rememberMe(FALSE, FALSE))
				{
					echo "<BR>" . $_COOKIE["AUTHID"] . "<BR>";
					echo "<BR>" . "TEST PASSED 1.1." . "<BR>";
				}
				else
					echo "<BR>TEST FAILED 1." . "<BR>";
			}
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
	
	public function testRememberMe_2()
	{
		try
		{
			$this->obj->setAutomaticLoginTimePeriod(3);
			
			if (!isset($_COOKIE["AUTHID"]))
			{
				$this->obj->rememberMe(FALSE, TRUE);
			}
			else
			{
				$authID = $_COOKIE["AUTHID"];
				
				if (!$this->obj->rememberMe())	//At this point the cookie will be deleted because the time frame has passed.
				{
					$query = "SELECT `AUTH_ID` FROM AUTH_STORAGE WHERE USERID = ? AND `AUTH_ID` = ?";
					$args = array($this->_userID->getUserID(), $authID);
					$result = $this->_handler->SQL($query, $args);
					
					if (count($result) != 0)
						echo "<BR>" . "TEST FAILED 2.1." . "<BR>";
					else
						echo "<BR>" . "TEST PASSED 2.1." . "<BR>";
				}
				else
					echo "<BR>TEST FAILED 2." . "<BR>";
			}
		}
		catch (\Exception $e)
		{
			echo "\n" . $e->getLine() . "-->";
			echo $e->getMessage() . "\n";
		}
	}
}

$myTest = new TestRememberMe();
$myTest->setUp();

$myTest->testRememberMe_1();

$myTest->testRememberMe_2();

?>
