<?php
namespace phpsec;



/**
 * Required Files
 */
require_once(__DIR__ . '/../core/random.php');
require_once(__DIR__ . '/../core/time.php');



/**
 * Parent Exception Class
 */
class SessionException extends \Exception {}



/**
 * Child Exception Classes
 */
class SessionNotFoundException extends SessionException {}	//Use of sessions before setting them.
class NullUserException extends SessionException {}		//Null User passed.
class SessionExpired extends SessionException {}		//Session has Expired.



class Session
{



	/**
	 * The session ID.
	 * @var string
	 */
	protected $session = null;



	/**
	 * The user ID.
	 * @var string
	 */
	protected $userID = null;



	/**
	 * Idle period. If the user is inactive for more than this period, the session must expire.
	 * @var int
	 */
	public static $inactivityMaxTime = 1800; //30 min.



	/**
	 * Session Aging. After this period, the session must expire no matter what.
	 * @var int
	 */
	public static $expireMaxTime = 604800; //1 week.

	/**
	 * sweep ratio for probablity function in expired session removal process
	 * @var decimal
	 */
	public static $SweepRatio = 0.75;

	/**
	 *  Function to sweep expired session from db
	 */
	private function clearExpiredSession( $force = false )
	{
		if (!$force) if (rand ( 0, 1000 ) / 1000.0 > self::$SweepRatio) return;

		$timeLimit = time() - self::$inactivityMaxTime;
		/*
		 * query to delete expired session from both SESSION and SESSION_DATA table
		 */
		$result = SQL("SELECT `SESSION_ID` FROM `SESSION` WHERE `LAST_ACTIVITY` < ?",array($timeLimit));
		foreach($result as $id)
		{
			SQL("DELETE FROM `SESSION_DATA` WHERE `SESSION_ID` = ?",array($id['SESSION_ID']));
			SQL("DELETE FROM `SESSION` WHERE `SESSION_ID` = ?",array($id['SESSION_ID']));
		}
	}

	/**
	 * Function to check if sessionID is set for this user or not.
	 * @throws SessionNotFoundException
	 */
	private function checkIfSessionIDisSet()
	{
		if ( ($this->session == "") || ($this->session == null) )
		{
			throw new SessionNotFoundException("ERROR: No session is set for this user.");
		}
	}



	/**
	 * To return the current session ID.
	 * @return string		The session ID
	 * @throws SessionExpired	Thrown when the session has expired
	 */
	public function getSessionID()
	{
		if ($this->inactivityTimeout() || $this->expireTimeout())
		{
			throw new SessionExpired("ERROR: This session has expired.");
		}

		return $this->session;
	}



	/**
	 * To return the current User ID.
	 * @return string | NULL
	 */
	public function getUserID()
	{
		return $this->userID;
	}



	/**
	 * To create a new Session ID for the given user.
	 * @param string $userID	The id of the user
	 * @return string		The new session ID of the current user.
	 */
	public function newSession($userID)
	{
		if ( ($userID == null) || ($userID == "") )
			throw new NullUserException("ERROR: UserID cannot be null.");

		$this->userID = $userID;
		$this->session = randstr(128); //generate a new random string for the session ID of length 32.

		$time = time(); //get the current time.
		SQL("INSERT INTO SESSION (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array($this->session, $time, $time, $this->userID));

		$this->updateUserCookies();

		/**
		 * Function to clear expired sessions
		 */
		$this->clearExpiredSession();
		return $this->session;
	}



	/**
	 * Function to get the session object from an old sessionID that we receive from the user's cookie.
	 * @return string | FALSE	Returns the sessionID or FALSE
	 * @throws SessionExpired	Thrown when the session has expired
	 */
	public function existingSession()
	{
		if (!isset($_COOKIE['SESSIONID']))	//If user cannot provide a session ID, then no session is present for this user. Hance return false
			return FALSE;

		$sessionID = $_COOKIE['SESSIONID'];	//get the session ID from the user cookie

		$result = SQL("SELECT `USERID` FROM SESSION WHERE `SESSION_ID` = ?", array($sessionID));	//match if the session ID received from the user is same as what was issued to them. If same, then the session ID stored in our DB must match with the one we received
		if (count($result) != 1)	//a suitable match is not found
		{
			$this->updateUserCookies(TRUE);		//delete the cookie from user's browser
			return FALSE;
		}

		//set local variables
		$this->session = $sessionID;
		$this->userID = $result[0]['USERID'];

		//check if the session ID's have expired
		if ($this->inactivityTimeout() || $this->expireTimeout())
		{
			throw new SessionExpired("ERROR: This session has expired.");
		}

		$this->updateLastActivity();

		return $this->session;
	}



	/**
	 * Function to update/delete user session cookies
	 * @param boolean $deleteCookie		True indicates this function to DELETE the cookie from the user's browser. False indicates this function to CREATE the cookie in user's browser.
	 */
	public function updateUserCookies($deleteCookie = FALSE)
	{
		if ($deleteCookie === FALSE)
		{
			\setcookie("SESSIONID", $this->session, time() + Session::$expireMaxTime, null, null, FALSE, TRUE);
		}
		else
		{
			\setcookie("SESSIONID", NULL, time() - Session::$expireMaxTime, null, null, FALSE, TRUE);
		}
	}



	/**
	 * To get all session IDs for the user. Total count of session is also the indication of how many devices the user is currently logged in from because each valid session refers to one device.
	 * @param string $userID	User-ID of the user
	 * @return string[] | FALSE	Array containing all the session ID's of that user or FALSE in case no record is found
	 */
	public static function getAllSessions($userID)
	{
		$result = SQL("SELECT `SESSION_ID` FROM SESSION WHERE USERID = ?", array($userID));

		if (count($result) != 0)
		{
			return $result;
		}

		return FALSE;
	}



	/**
	 * To store data in current session.
	 * @param string $key			The 'name' of the data. The actual data will be referenced by this name.
	 * @param string $value			The actual data that needs to be stored
	 * @throws SessionNotFoundException	Thrown when trying to store data when no session ID is set
	 * @throws SessionExpired		Thrown when the session has expired
	 */
	public function setData($key, $value)
	{
		$this->checkIfSessionIDisSet();

		//check before setting data, if the session has expired.
		if ($this->inactivityTimeout() || $this->expireTimeout()) {
			throw new SessionExpired("ERROR: This session has expired.");
		}

		//check if the key given by the user has already been set. If yes, then the value needs to be replaced and new record for key=>value is NOT needed.
		if (count($this->getData($key)) == 1)
		{
			SQL("UPDATE SESSION_DATA SET `VALUE` = ? WHERE `KEY` = ? AND SESSION_ID = ?", array($value, $key, $this->session));
		}
		else //If the key is not found, then a new record of key=>value pair needs to be created.
		{
			SQL("INSERT INTO SESSION_DATA (`SESSION_ID`, `KEY`, `VALUE`) VALUES (?, ?, ?)", array($this->session, $key, $value));
		}
	}



	/**
	 * To retrieve data from current session
	 * @param string $key			The name of the data from which it is referenced
	 * @return string[]			The key=>value pair. Empty array will be returned in case no data is found
	 * @throws SessionNotFoundException	Thrown when trying to retrive data when sessionID is not set
	 * @throws SessionExpired		Thrown when the session has expired
	 */
	public function getData($key)
	{
		$this->checkIfSessionIDisSet();

		//check before retrieving data, if the session has expired.
		if ($this->inactivityTimeout() || $this->expireTimeout()) {
			throw new SessionExpired("ERROR: This session has expired.");
		}

		$this->updateLastActivity();

		$result = SQL("SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE `SESSION_ID` = ? and `KEY` = ?", array($this->session, $key));
		return $result;
	}



	/**
	 * To check if inactivity time has passed for this session.
	 * @return boolean	Returns True if inactivity time has passed. False otherwise
	 */
	public function inactivityTimeout()
	{
		if ( ($this->session == null) || ($this->session == "") )
			return TRUE;

		$result = SQL("SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?", array($this->session));

		if (count($result) == 1)
		{
			$lastActivityTime = (int)$result[0]['LAST_ACTIVITY']; //get the last time when the user was active.

			$difference = time() - $lastActivityTime; //get difference betwen the current time and the last active time.

			if ($difference > Session::$inactivityMaxTime) //if difference exceeds the inactivity time, destroy the session.
			{
				$this->destroySession();
				return TRUE;
			}

			return FALSE;
		}
		else
		{
			$this->session = NULL;
			return TRUE;
		}
	}



	/**
	 * To check if expiry time has passed for this session.
	 * @return boolean	Returns True if the expiry time has passed for this user. False otherwise
	 */
	public function expireTimeout()
	{
		if ( ($this->session == null) || ($this->session == "") )
			return TRUE;

		$result = SQL("SELECT `DATE_CREATED` FROM SESSION WHERE `SESSION_ID` = ?", array($this->session));

		if (count($result) == 1)
		{
			$lastActivityTime = (int)$result[0]['DATE_CREATED']; //get the date when this session was created.

			$difference = time() - $lastActivityTime; //get difference betwen the current time and the creation time.

			if ($difference > Session::$expireMaxTime) //if difference exceeds the expiry time, destroy the session.
			{
				$this->destroySession();
				return TRUE;
			}

			return FALSE;
		}
		else
		{
			$this->session = NULL;
			return TRUE;
		}
	}



	/**
	 * To refresh the session ID of the current session. This will update the last time that the user was active and the session creation date to the current time. The essence is to make the session ID look like it was just created now.
	 * @return string			Returns the new/current sessionID and update the browser's cookies
	 * @throws SessionNotFoundException	Thrown when trying to refresh session when no session ID is set
	 * @throws SessionExpired		Thrown when the session has expired
	 */
	public function refreshSession()
	{
		$this->checkIfSessionIDisSet();

		//check for session expiry.
		if ($this->inactivityTimeout() || $this->expireTimeout()) {
			throw new SessionExpired("ERROR: This session has expired.");
		}

		//exchange the old session's creation date and the last activity time with the current time.
		SQL("UPDATE SESSION SET `DATE_CREATED` = ? , `LAST_ACTIVITY` = ? WHERE SESSION_ID = ?", array(time(), time(), $this->session));
		$this->updateUserCookies();
		return $this->session;
	}



	/**
	 * To destroy the current Session.
	 * @throws SessionNotFoundException	Thrown when trying to destroy a session when one does not exists
	 */
	public function destroySession()
	{
		$this->checkIfSessionIDisSet();

		SQL("DELETE FROM `SESSION_DATA` WHERE `SESSION_ID` = ?", array($this->session));	//delete all data associated with this session ID.
		SQL("DELETE FROM SESSION WHERE `SESSION_ID` = ?", array($this->session));	//delete this sessiom ID.

		$this->session = null;
		$this->updateUserCookies(TRUE);
	}



	/**
	 * To destroy all the sessions associated with the current User.
	 */
	public static function destroyAllSessions($userID)
	{
		$allSessions = Session::getAllSessions($userID); //get all sessions associated with this user.

		foreach ($allSessions as $args)		// For each of those sessions, delete data stored by those sessions and then delete the session IDs.
		{
			SQL("DELETE FROM `SESSION_DATA` WHERE `SESSION_ID` = ?", array($args['SESSION_ID']));
			SQL("DELETE FROM SESSION WHERE `SESSION_ID` = ?", array($args['SESSION_ID']));
		}
	}



	/**
	 * To promote/demote a session. This essentially destroys the current session ID and issues a new session ID.
	 * @return string			Returns the new sessionID and updates user cookies
	 * @throws SessionNotFoundException	Thrown when trying to roll a session when sessionID is not set
	 * @throws SessionExpired		Thrown when the session has expired
	 */
	public function rollSession()
	{
		$this->checkIfSessionIDisSet();

		//check for session expiry.
		if ($this->inactivityTimeout() || $this->expireTimeout()) {
			throw new SessionExpired("ERROR: This session has expired.");
		}

		//get all the data that is stored by this session.
		$result = SQL("SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE SESSION_ID = ?", array($this->session));

		//destroy the current session.
		$this->destroySession();

		//create a new session.
		$newSession = $this->newSession($this->userID);

		//copy all the previous data to this new session.
		foreach ($result as $arg) {
			$this->setData($arg['KEY'], $arg['VALUE']);
		}

		$this->updateUserCookies();
		return $newSession;
	}



	/**
	 * Functoin to get the userID from a sessionID
	 * @param string $sessionID	The session ID for which matching userID is needed
	 * @return boolean | string	Returns the userID if match found. False otherwise
	 */
	public static function getUserIDFromSessionID($sessionID)
	{
		$result = SQL("SELECT `USERID` FROM SESSION WHERE `SESSION_ID` = ?", array($sessionID));

		if (count($result) == 1)
		{
			return $result[0]['USERID'];
		}
		else
			return FALSE;
	}


	/**
	 * Function to update SESSION_ID LastActivity
	 * @throws SessionNotFoundException	Thrown when trying to store data when no session ID is set
	 * @throws SessionExpired		Thrown when the session has expired
	 */
	public function updateLastActivity()
	{
		$this->checkIfSessionIDisSet();

		//check for session expiry.
		if ($this->inactivityTimeout() || $this->expireTimeout())
			throw new SessionExpired("ERROR: This session has expired.");

		SQL("UPDATE `SESSION` SET `LAST_ACTIVITY` = ? WHERE `SESSION_ID` = ?",array(time(), $this->session));
	}

}

?>
