<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/db/dbmanager.php";
require_once "../../../libs/core/random.php";
require_once "../../../libs/core/time.php";
require_once "../../../libs/auth/user.php";
require_once "../../../libs/session/session.php";

class SessionTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * To store Session Objects.
	 * @var type Object Array
	 */
	public $session = array();
	
	
	/**
	 * To store User Objects.
	 * @var type Object Array
	 */
	public $user = array();
	
	
	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		time("RESET");
		
		try
		{
			DatabaseManager::connect (new DatabaseConfig('pdo_mysql','OWASP','root','testing'));	//create a new Db handler.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}

		try
		{
			//Create users.
			$this->user[0] = User::newUserObject(\phpsec\Rand::generateRandom(10), "resting");
			$this->user[1] = User::newUserObject(\phpsec\Rand::generateRandom(10), "owasp");
		}
		catch(\Exception $e)
		{
			echo $e->getMessage();
		}
		
		try
		{
			//create new sessions associated with each user.
			$this->session[0] = new Session($this->user[0]->getUserID());	//session for user 0.
			$this->session[1] = new Session($this->user[0]->getUserID());	//session for user 0.
			$this->session[2] = new Session($this->user[1]->getUserID());	//session for user 1.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if storage and retrieval is having properly.
	 */
	public function testDataStorage()
	{
		try
		{
			$key = "project";
			$value = "phpsec";
			
			$this->session[0] -> setData($key, $value);	//store data for session 0.
			$arrayReturned = $this->session[0] -> getData($key);	//get data for session 0.
			$valueReturned = $arrayReturned[0]['VALUE'];
			
			$this -> assertTrue($value == $valueReturned);	//the stored data must be equal to the retrieved data.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if multiple values can be inserted to a single key.
	 */
	public function testMultipleInsertionsOnOneKey()
	{
		try
		{
			$key = "OWASP";
			$value = "data1";
			$this->session[0] -> setData($key, $value);	//store some data to session 0.

			$value = "data2";
			$this->session[0] -> setData($key, $value);	//store another data to session 0 with same key.

			$value = "data3";
			$this->session[0] -> setData($key, $value);	//store another data to session 0 with same key.

			$arrayReturned = $this->session[0] -> getData($key);	//retrive the value associated with the Key in session 0.
			$valueReturned = $arrayReturned[0]['VALUE'];

			$this -> assertTrue($value == $valueReturned);	//the value retrieved must be equal the last value set i.e. data 3.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if NULL is returned if incorrect key is passed to retrive data.
	 */
	public function testIfKeyNotExists()
	{
		try
		{
			$key = "project";
			$value = "phpsec";
			
			$key2 = "this_will_not_be_stored";
			
			$this->session[0] -> setData($key, $value);	//set value with key.
			$arrayReturned = $this->session[0] -> getData($key2);	//retrive value with key2.
			
			$this -> assertTrue(count($arrayReturned) == 0);	//No value must be returned since key is wrong.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if data are only accessible with correct sessions and keys.
	 */
	public function testAccessibility()
	{
		try
		{
			$key = "project";
			$value = "phpsec";
			
			$key2 = "OWASP";
			$value2 = "security";
			
			$key3 = "Google";
			$value3 = "GSOC";
			
			$this->session[0] -> setData($key, $value);	//set key=>value for session 0, user 0.
			$this->session[1] -> setData($key2, $value2);	//set key=>value for session 1, user 0.
			$this->session[2] -> setData($key3, $value3);	//set key=>value for session 2, user 1.
			
			$arrayReturned1 = $this->session[0] -> getData($key);	//should be accessible because correct user is using correct session wit correct key.
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
	
	
	/**
	 * To check if inactivityTime is working or not.
	 */
	public function testInactivityTimeout()
	{
		try
		{
			time("SET", 1380502880);	//set current time to a very far future.
			
			$this -> assertTrue( $this->session[1] -> inactivityTimeout() );	//By that time, the session must expire.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * To check if expiryTime is working or not.
	 */
	public function testExpireTimeout()
	{
		try
		{
			time("SET", 1380502880);	//set current time to a very far future.
			
			$this -> assertTrue( $this->session[2] -> expireTimeout() );	//By that time, the session must expire.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * Function to check if rollSession works or not.
	 */
	public function testRollSession()
	{
		try
		{
			$key = "PHP";
			$value = "library";
			$this->session[0]->setData($key, $value);	//set data for session 0.
			
			$oldSession = $this->session[0]->getSessionID();
			$this->session[0]->rollSession();	//roll the session.
			$newSession = $this->session[0]->getSessionID();
			
			$result = $this->session[0]->getData($key);	//to check if after rolling session, we get the same data or not.
			$valueAccessed = $result[0]['VALUE'];
			
			$this -> assertTrue( ($oldSession != $newSession) && ($valueAccessed == $value) );	//The value must be accessible.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * Function to check if refreshSession works.
	 */
	public function testRefreshSession()
	{
		try
		{
			$newTime = time("SYS") + 123;
			time("SET", $newTime);	//set a new future time.
			
			$this->session[0]->refreshSession();	//refresh the session.
			
			$result = SQL("SELECT LAST_ACTIVITY FROM SESSION WHERE SESSION_ID = ?", array( "{$this -> session[0] -> getSessionID()}" ));
			$sessionActivityTime = $result[0]['LAST_ACTIVITY'];
			
			$this -> assertTrue( (int)$sessionActivityTime >= $newTime );	//the new time for the session must be greater than or equal to the fake time we set.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * Function to check if the current session can be destroyed or not.
	 */
	public function testDestroySession()
	{
		try
		{
			$this->session[0]->destroySession();
			
			$this -> assertTrue( $this->session[0]->getSessionID() === null );	//If session is deleted, then true is returned.
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/**
	 * Function to check if all sessions can be destroyed for the current user.
	 */
	public function testDestroyAllSessions()
	{
		try
		{
			$this->session[0]->destroyAllSessions();
			
			$result = SQL("SELECT TOTAL_SESSIONS FROM USER WHERE USERID = ?", array( "{$this -> session[0] ->getUserID()}" ));
			$totalSessions = $result[0]['TOTAL_SESSIONS'];
			
			$this -> assertTrue( $totalSessions == 0 );	//The total sessions must be 0 for this user after this operation.
			
		}
		catch(\Exception $e)
		{
			echo "Line Number is: " . $e->getLine() . "\n";
			echo $e -> getMessage();
		}
	}
	
	
	/*
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		try
		{
			//destroy all the created sessions.
			if ($this->session[0]->getSessionID() != null)
				$this->session[0] ->destroySession();
			if ($this->session[1]->getSessionID() != null)
				$this->session[1] ->destroySession();
			if ($this->session[2]->getSessionID() != null)
				$this->session[2] ->destroySession();
			
			//delete all the created users.
			$this->user[0] ->deleteUser();
			$this->user[1] ->deleteUser();
			
			//delete the connection to the DB.
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
