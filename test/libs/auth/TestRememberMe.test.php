<?php
namespace phpsec;

ini_set("display_errors", 1);

echo "<PRE>";

/**
 *					*****************IMPORTANT*****************************
 * 
 * This class is to test the "rememberMe" function in the file "/var/www/phpsec/libs/auth/user.php".
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
 * 3) Check your browser cookies. You should NOT see a cookie of name "rash" under your TEST_SITE (localhost).
 * 
 * 
 * 
 */

require_once "../../../libs/db/dbmanager.php";
require_once '../../../libs/core/random.php';
require_once '../../../libs/core/time.php';
require_once '../../../libs/auth/user.php';

class TestRememberMe
{
	private $user = "";
	
	public function setUp()
	{
		try
		{
			DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP','root','testing'));	//create DB connection.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
		try
		{
			BasicPasswordManagement::$hashAlgo = "haval256,5";
			$this->user = User::newUserObject("rash", "testing");
		}
		catch (\Exception $e)
		{
			$this->user = User::existingUserObject("rash", "testing");
		}
	}
	
	public function testRememberMe_1()
	{
		try
		{
			$this->user->rememberMe(FALSE, FALSE);
			
			if (isset($_COOKIE["AUTHID"]))
			{
				if ($this->user->rememberMe(FALSE, FALSE))
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
			Session::$expireMaxTime = 3;
			
			if (!isset($_COOKIE["AUTHID"]))
			{
				$this->user->rememberMe(FALSE, TRUE);
			}
			else
			{
				$authID = $_COOKIE["AUTHID"];
				
				if (!$this->user->rememberMe())	//At this point the cookie will be deleted because the time frame has passed.
				{
					$result = SQL("SELECT `SESSION_ID` FROM SESSION WHERE USERID = ? AND `SESSION_ID` = ?", array($this->user->getUserID(), $authID));
					
					if (count($result) != 0)
						echo "<BR>" . "TEST FAILED 2.1." . "<BR>";
					else
						echo "<BR>" . "TEST PASSED 2.1." . "<BR>";
				}
				else
					echo "<BR>TEST FAILED 2." . "<BR>";
			}
			
			$this->user->deleteUser();
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
