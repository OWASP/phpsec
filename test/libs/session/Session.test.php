<?php
namespace phpsec;

require_once "../../../libs/db/adapter/pdo_mysql.php";
require_once "../../../libs/core/Rand.class.php";	//at later time you won't need this because then users will be created by a different method.
require_once "../../../libs/auth/User.class.php";
require_once "../../../libs/session/Session.class.php";

class SessionTest extends \PHPUnit_Framework_TestCase
{
	public $session = array();
	public $user = array();
	public $conn = null;
	
	public function setUp()
	{
		Time::$realTime = true;

		try
		{
			$this->conn = new \phpsec\Database_pdo_mysql ('OWASP', 'root', 'testing');
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}

		try
		{
			$this->user[0] = User::newUserObject($this->conn, \phpsec\Rand::generateRandom(10), "resting");
			$this->user[1] = User::newUserObject($this->conn, \phpsec\Rand::generateRandom(10), "owasp");
		}
		catch(\Exception $e)
		{
			echo $e->getMessage();
		}
		
		try
		{
			$this->session[0] = new Session($this->conn, $this->user[0]);
			$this->session[1] = new Session($this->conn, $this->user[0]);
			$this->session[2] = new Session($this->conn, $this->user[1]);
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testDataStorage()
	{
		Time::$realTime = true;
		
		try
		{
			$key = "project";
			$value = "phpsec";
			
			$this->session[0] -> setData($key, $value);
			$arrayReturned = $this->session[0] -> getData($key);
			$valueReturned = $arrayReturned[0]['VALUE'];
			
			$this -> assertTrue($value == $valueReturned);
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testMultipleInsertionsOnOneKey()
	{
		Time::$realTime = true;
		
		try
		{
			$key = "OWASP";
			$value = "data1";
			$this->session[0] -> setData($key, $value);

			$value = "data2";
			$this->session[0] -> setData($key, $value);

			$value = "data3";
			$this->session[0] -> setData($key, $value);

			$arrayReturned = $this->session[0] -> getData($key);
			$valueReturned = $arrayReturned[0]['VALUE'];

			$this -> assertTrue($value == $valueReturned);
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testIfKeyNotExists()
	{
		Time::$realTime = true;
		
		try
		{
			$key = "project";
			$value = "phpsec";
			
			$key2 = "this_will_not_be_stored";
			
			$this->session[0] -> setData($key, $value);
			$arrayReturned = $this->session[0] -> getData($key2);
			
			$this -> assertTrue(count($arrayReturned) == 0);
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testAccessibility()
	{
		Time::$realTime = true;
		
		try
		{
			$key = "project";
			$value = "phpsec";
			
			$key2 = "OWASP";
			$value2 = "security";
			
			$key3 = "Google";
			$value3 = "GSOC";
			
			$this->session[0] -> setData($key, $value);
			$this->session[1] -> setData($key2, $value2);
			$this->session[2] -> setData($key3, $value3);
			
			$arrayReturned1 = $this->session[0] -> getData($key);	//should be accessible.
			$arrayReturned2 = $this->session[0] -> getData($key2);	//should NOT be accessible even though same user but different sessions.
			$arrayReturned3 = $this->session[0] -> getData($key3);	//should NOT be accessible because different users.
			
			$this -> assertTrue( (count($arrayReturned1) != 0) && (count($arrayReturned2) == 0) && (count($arrayReturned3) == 0) );
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testInactivityTimeout()
	{
		try
		{
			Time::$realTime = false;
			Time::setTime(1380502880);
			
			$this -> assertTrue( $this->session[1] -> inactivityTimeout() );	//The funtion is tested with session[1] so that it does not deletes the session prematurely for other functions to behave irradically.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testExpireTimeout()
	{
		try
		{
			Time::$realTime = false;
			Time::setTime(1380502880);
			
			$this -> assertTrue( $this->session[2] -> expireTimeout() );	//The funtion is tested with session[2] so that it does not deletes the session prematurely for other functions to behave irradically.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testRollSession()
	{
		try
		{
			Time::$realTime = true;
			
			$key = "PHP";
			$value = "library";
			$this->session[0]->setData($key, $value);
			
			$oldSession = $this->session[0]->getSessionID();
			$this->session[0]->rollSession();
			$newSession = $this->session[0]->getSessionID();
			
			$result = $this->session[0]->getData($key);	//to check if after rolling session, we get the same data or not.
			$valueAccessed = $result[0]['VALUE'];
			
			$this -> assertTrue( ($oldSession != $newSession) && ($valueAccessed == $value) );
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testRefreshSession()
	{
		try
		{
			Time::$realTime = true;
			$newTime = Time::time() + 123;
			
			Time::$realTime = false;
			Time::setTime($newTime);
			
			$this->session[0]->refreshSession();
			
			$query = "SELECT LAST_ACTIVITY FROM SESSION WHERE SESSION_ID = ?";
			$args = array( "{$this -> session[0] -> getSessionID()}" );
			$result = $this -> conn -> SQL($query, $args);
			$sessionActivityTime = $result[0]['LAST_ACTIVITY'];
			
			$this -> assertTrue( (int)$sessionActivityTime >= $newTime );
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testDestroySession()
	{
		try
		{
			Time::$realTime = true;
			
			$this->session[0]->destroySession();
			
			$this -> assertTrue( $this->session[0]->getSessionID() === null );
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function testDestroyAllSessions()
	{
		try
		{
			Time::$realTime = true;
			
			$this->session[0]->destroyAllSessions();
			
			$query = "SELECT TOTAL_SESSIONS FROM USER WHERE USERID = ?";
			$args = array( "{$this -> session[0] ->getUserID()}" );
			$result = $this -> conn -> SQL($query, $args);
			$totalSessions = $result[0]['TOTAL_SESSIONS'];
			
			$this -> assertTrue( $totalSessions == 0 );
			
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	public function tearDown()
	{
		try
		{
			Time::$realTime = true;
			
			if ($this->session[0]->getSessionID() != null)
				$this->session[0] ->destroySession();
			if ($this->session[1]->getSessionID() != null)
				$this->session[1] ->destroySession();
			if ($this->session[2]->getSessionID() != null)
				$this->session[2] ->destroySession();
			
			$this->user[0] ->deleteUser();
			$this->user[1] ->deleteUser();
			
			$this->conn = null;
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
}

?>