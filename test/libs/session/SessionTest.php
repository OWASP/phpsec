<?php
namespace phpsec;
ob_start();



/**
 * Required Files.
 */
require_once __DIR__ . "/../testconfig.php";
require_once __DIR__ . "/../../../libs/core/time.php";
require_once __DIR__ . "/../../../libs/auth/user.php";
require_once __DIR__ . "/../../../libs/session/session.php";



class SessionTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Session Object Array.
	 * @var Array
	 */
	public $session = array();



	/**
	 * User Object Array.
	 * @var Array
	 */
	public $user = array();



	/**
	 * Function to be run before every test*() functions.
	 */
	public function setUp()
	{
		time("RESET");

		//Create users.
		User::newUserObject("abcd", "resting", "rac130@pitt.edu");
		User::activateAccount("abcd");
		$this->user[0] = User::existingUserObject("abcd", "resting");

		//Create users.
		User::newUserObject("efgh", "resting", "rac130@pitt.edu");
		User::activateAccount("efgh");
		$this->user[1] = User::existingUserObject("efgh", "resting");

		//create new sessions associated with each user.
		$this->session[0] = new Session();
		$this->session[1] = new Session();
		$this->session[2] = new Session();

		$this->session[0]->newSession($this->user[0]->getUserID());	//session for user 0.
		$this->session[1]->newSession($this->user[0]->getUserID());	//session for user 0.
		$this->session[2]->newSession($this->user[1]->getUserID());	//session for user 1.
	}



	/*
	 * This function will run after each test*() function has run. Its job is to clean up all the mess creted by other functions.
	 */
	public function tearDown()
	{
		//destroy all the created sessions.
		try {
			$this->session[0]->getSessionID();
			$this->session[0]->destroySession();
		}
		catch (\phpsec\SessionExpired $e) {}

		try {
			$this->session[1]->getSessionID();
			$this->session[1]->destroySession();
		}
		catch (\phpsec\SessionExpired $e) {}

		try {
			$this->session[2]->getSessionID();
			$this->session[2]->destroySession();
		}
		catch (\phpsec\SessionExpired $e) {}

		//delete all the created users.
		$this->user[0]->deleteUser();
		$this->user[1]->deleteUser();
	}



	/**
	 * Function to test if we can get all sessions
	 */
	public function testGetAllSessions()
	{
		$this->assertTrue(count(Session::getAllSessions($this->session[0]->getUserID())) == 2);
	}



	/**
	 * To check if storage and retrieval is having properly.
	 */
	public function testDataStorage()
	{
		$key = "project";
		$value = "phpsec";

		$this->session[0]->setData($key, $value); //store data for session 0.
		$arrayReturned = $this->session[0]->getData($key); //get data for session 0.
		$valueReturned = $arrayReturned[0]['VALUE'];

		$this->assertTrue($value == $valueReturned); //the stored data must be equal to the retrieved data.
	}



	/**
	 * To check if multiple values can be inserted to a single key.
	 */
	public function testMultipleInsertionsOnOneKey()
	{
		$key = "OWASP";
		$value = "data1";
		$this->session[0]->setData($key, $value); //store some data to session 0.

		$value = "data2";
		$this->session[0]->setData($key, $value); //store another data to session 0 with same key.

		$value = "data3";
		$this->session[0]->setData($key, $value); //store another data to session 0 with same key.

		$arrayReturned = $this->session[0]->getData($key); //retrive the value associated with the Key in session 0.
		$valueReturned = $arrayReturned[0]['VALUE'];

		$this->assertTrue($value == $valueReturned); //the value retrieved must be equal the last value set i.e. data 3.
	}



	/**
	 * To check if NULL is returned if incorrect key is passed to retrive data.
	 */
	public function testIfKeyNotExists()
	{
		$key = "project";
		$value = "phpsec";

		$key2 = "this_will_not_be_stored";

		$this->session[0]->setData($key, $value); //set value with key.
		$arrayReturned = $this->session[0]->getData($key2); //retrive value with key2.

		$this->assertTrue(count($arrayReturned) == 0); //No value must be returned since key is wrong.
	}



	/**
	 * To check if data are only accessible with correct sessions and keys.
	 */
	public function testAccessibility()
	{
		$key = "project";
		$value = "phpsec";

		$key2 = "OWASP";
		$value2 = "security";

		$key3 = "Google";
		$value3 = "GSOC";

		$this->session[0]->setData($key, $value); //set key=>value for session 0, user 0.
		$this->session[1]->setData($key2, $value2); //set key=>value for session 1, user 0.
		$this->session[2]->setData($key3, $value3); //set key=>value for session 2, user 1.

		$arrayReturned1 = $this->session[0]->getData($key); //should be accessible because correct user is using correct session wit correct key.
		$arrayReturned2 = $this->session[0]->getData($key2); //should NOT be accessible even though same user but different sessions.
		$arrayReturned3 = $this->session[0]->getData($key3); //should NOT be accessible because different users.

		$this->assertTrue((count($arrayReturned1) != 0) && (count($arrayReturned2) == 0) && (count($arrayReturned3) == 0));
	}



	/**
	 * To check if inactivityTime is working or not.
	 */
	public function testInactivityTimeout()
	{
		time("SET", time() + 86400*1000); //set current time to a very far future.
		$this->assertTrue($this->session[1]->inactivityTimeout()); //By that time, the session must expire.
	}



	/**
	 * To check if expiryTime is working or not.
	 */
	public function testExpireTimeout()
	{
		time("SET", time() + 86400*1000); //set current time to a very far future.
		$this->assertTrue($this->session[2]->expireTimeout()); //By that time, the session must expire.
	}



	/**
	 * Function to check if rollSession works or not.
	 */
	public function testRollSession()
	{
		$key = "PHP";
		$value = "library";
		$this->session[0]->setData($key, $value); //set data for session 0.

		$oldSession = $this->session[0]->getSessionID();
		$this->session[0]->rollSession(); //roll the session.
		$newSession = $this->session[0]->getSessionID();

		$result = $this->session[0]->getData($key); //to check if after rolling session, we get the same data or not.
		$valueAccessed = $result[0]['VALUE'];

		$this->assertTrue(($oldSession != $newSession) && ($valueAccessed == $value)); //The value must be accessible.
	}



	/**
	 * Function to check if refreshSession works.
	 */
	public function testRefreshSession()
	{
		$currentTime = time();
		$this->session[0]->refreshSession(); //refresh the session.

		$result = SQL("SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?", array($this->session[0]->getSessionID()));
		$sessionActivityTime = $result[0]['LAST_ACTIVITY'];

		$this->assertTrue((int)$sessionActivityTime >= $currentTime); //the new time for the session must be greater than or equal to the fake time we set.
	}



	/**
	 * Function to check if all sessions can be destroyed for the current user.
	 */
	public function testDestroyAllSessions()
	{
		//destroy all sessions
		Session::destroyAllSessions($this->session[0]->getUserID());
		//try to get all sessions. Must return FALSE since no records were found
		$allSessions = Session::getAllSessions($this->session[0]->getUserID());

		$this->assertFalse($allSessions);
	}



	/**
	 * Function to check if previous sessionIDs can be revived if their expiry time has not passed.
	 */
	public function testExistingSession()
	{
		$_COOKIE['SESSIONID'] = $this->session[0]->getSessionID();	//imitate the cookie variable because phpunit can't set cookies in browser.

		$myNewSession = new Session();
		$sessionID1 = $myNewSession->existingSession();
		$experiment1 = ($sessionID1 == $this->session[0]->getSessionID());	//Since session not expired, the old and the new session, both must be same.

		time("SET", time() + 86400*100);	//set time to some distant future time so that the session will expire
		try
		{
			$sessionID2 = $myNewSession->existingSession();
		}
		catch (SessionExpired $e)	//exception must be thrown here because the session has expired
		{
			$experiment2 = TRUE;
			$this->assertTrue($experiment1 && $experiment2);
		}
	}

	/**
	 * Function to test LastActivity Update on existing
	 */
	public function testLastActivity()
	{
		$this->session[0]->refreshSession(); //refresh the session.
		$result = SQL("SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?", array($this->session[0]->getSessionID()));
		$sessionActivityTime = $result[0]['LAST_ACTIVITY'];
		sleep(1);
		$this->session[0]->setData("hi",123);
		$result = SQL("SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?", array($this->session[0]->getSessionID()));
		$sessionActivityTime2 = $result[0]['LAST_ACTIVITY'];
		$this->assertTrue((int)$sessionActivityTime != (int)$sessionActivityTime2); //the new time for the session must be greater than or equal to the fake time we set.
	}

}

?>
