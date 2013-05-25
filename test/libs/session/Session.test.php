<?php
namespace phpsec;

require_once __DIR__ . "/../../../libs/db/adapter/pdo_mysql.php";
require_once __DIR__ . "/../../../libs/core/Rand.class.php";	//at later time you won't need this.
require_once __DIR__ . "/../../../libs/core/Exception.class.php";
require_once __DIR__ . "/../../../libs/auth/User.class.php";
require_once __DIR__ . "/../../../libs/session/Session.class.php";

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
			$this->user[0] = new User($this->conn, \phpsec\Rand::generateRandom(10));
			$this->user[1] = new User($this->conn, \phpsec\Rand::generateRandom(10));
		}
		catch(\Exception $e)
		{
			echo $e->getMessage();
		}
		
		try
		{
			$this->session[0] = new Session($this->user[0], $this->conn);
			$this->session[1] = new Session($this->user[0], $this->conn);
			$this->session[2] = new Session($this->user[1], $this->conn);
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testDataStorage()
	{
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
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testInactivityTimeout()
	{
		try
		{
			Time::$realTime = false;
			Time::setTime(1369502880);
			
			$this -> assertTrue( $this->session[0] -> inactivityTimeout() );
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testExpireTimeout()
	{
		try
		{
			Time::$realTime = false;
			Time::setTime(1380502880);
			
			$this -> assertTrue( $this->session[0] -> expireTimeout() );
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testRefreshSession()
	{
		try
		{
			$fakeTime = 1385502880;
			
			Time::$realTime = false;
			Time::setTime($fakeTime);
			
			$this->session[0]->refreshSession();
			
			$query = "SELECT LAST_ACTIVITY FROM SESSION WHERE SESSION_ID = ?";
			$args = array( "{$this -> session[0] -> getSessionID()}" );
			$result = $this -> conn -> SQL($query, $args);
			$sessionActivityTime = $result[0]['LAST_ACTIVITY'];
			
			$this -> assertTrue( $sessionActivityTime >= $fakeTime );
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
	
	public function testRollSession()
	{
		try
		{
			Time::$realTime = true;
			
			$oldSession = $this->session[0]->getSessionID();
			$this->session[0]->rollSession();
			$newSession = $this->session[0]->getSessionID();
			
			$this -> assertTrue( $oldSession != $newSession );
		}
		catch(\Exception $e)
		{
			echo $e->getLine();
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
			echo $e->getLine();
			echo $e -> getMessage();
		}
	}
}

?>