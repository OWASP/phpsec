<?php
namespace phpsec;


/**
 * Required Files
 */
require_once (__DIR__ . '/../core/random.php');
require_once (__DIR__ . '/../core/time.php');


/**
 * Parent Exception Class
 */
class SessionException extends \Exception {}

/**
 * Child Exception Classes
 */
class SessionNotFoundException extends SessionException {}		//Use of sessions before setting them.
class NoUserFoundException extends SessionException {}			//User not found to be associated with a session.

class Session
{
	
	/**
	 * To hold the session ID.
	 * @var String
	 */
	protected $session = null;
	
	
	/**
	 * To hold the user ID.
	 * @var String
	 */
	protected $userID = null;	//for a session to present, there has to be a user. Without a user, a session cannot exist. So you have to create users in such a way that by the userID, the system can differentiate between a guest-user and a priviledged-user. Because you would need this distinction in RBAC.
	
	
	/**
	 * To hold the maximum time that is considered to be idle for user. After this period, the session must expire.
	 * @var int
	 */
	public static $inactivityMaxTime = 1800;	//30 min.
	
	
	/**
	 * To hold the maximum time that is considered as session age. After this period, the session must expire.
	 * @var int
	 */
	public static $expireMaxTime = 604800;	//1 week.
	
	
	/**
	 * Constructor to initialize a new session for some user.
	 * @param DatabaseObject $dbConn
	 * @param String $user
	 * @throws DBHandlerForSessionNotSetException
	 */
	public function __construct($user, $sessionName = "")
	{
		$this -> userID = $user;
		$this -> newSession($sessionName);	//get a new session ID for the current user.
	}
	
	
	/**
	 * To return the current session ID.
	 * @return String
	 */
	public function getSessionID()
	{
		return $this -> session;
	}
	
	
	/**
	 * To return the current User ID.
	 * @return String
	 */
	public function getUserID()
	{
		return $this -> userID;
	}
	
	
	/**
	 * To create a new Session ID for the current session.
	 * @return boolean
	 * @throws NoUserFoundException
	 */
	protected function newSession($sessionName = "")
	{
		if($this->userID == null)
			throw new NoUserFoundException("ERROR: No User was found. Session needs a user to be present.");

		if ($sessionName == "")
			$this -> session = randstr(32); //generate a new random string for the session ID of length 32.
		else
			$this -> session = $sessionName;
		
		$time = time();	//get the current time.

		SQL("INSERT INTO SESSION (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)", array("{$this -> session}", $time, $time, "{$this -> userID}"));

		$this -> updateTotalNoOfSessions();	//update the total number of sessions, since we just created one.

		return TRUE;
	}
	
	
	/**
	 * To update the total count of sessions for the current user.
	 * @return boolean
	 */
	protected function updateTotalNoOfSessions()
	{
		$result = $this -> getAllSessions();	//get all the session IDs for the current user.

		$totalCount = count($result);	//count the number of session IDs returned. Total number is the count of total number of sessions.

		SQL("UPDATE USER SET `TOTAL_SESSIONS` = ? WHERE USERID = ?", array($totalCount, $this -> userID));

		return TRUE;
	}
	
	
	/**
	 * To get all session IDs for the current user.
	 * @return StringArray
	 */
	public function getAllSessions()
	{
		$result = SQL("SELECT SESSION_ID FROM SESSION WHERE USERID = ?", array($this -> userID));

		return $result;
	}
	
	
	/**
	 * To set the data in the current session.
	 * @param String $key 
	 * @param String $value 
	 * @return boolean
	 * @throws SessionNotFoundException
	 */
	public function setData($key, $value)	
	{
		if($this -> session == null)
			throw new SessionNotFoundException("WARNING: No session is set for this user.");

		//check before setting data, if the session has expired.
		if($this->inactivityTimeout() || $this->expireTimeout())
		{
			echo "<BR>Session Timeout<BR>";
			$this->refreshSession();
		}

		//check if the key given by the user has already been set. If yes, then the value needs to be replaced and new record for key=>value is NOT needed.
		if ( count( $prevSession = $this -> getData($key) ) > 0 )
		{
			SQL("UPDATE SESSION_DATA SET `VALUE` = ? WHERE `KEY` = ? AND SESSION_ID = ?", array($value, $key, "{$this -> session}"));
		}
		else //If the key is not found, then a new record of key=>value pair needs to be created.
		{
			SQL("INSERT INTO SESSION_DATA (`SESSION_ID`, `KEY`, `VALUE`) VALUES (?, ?, ?)", array("{$this -> session}", $key, $value));
		}

		return TRUE;
	}
	
	
	/**
	 * To get the data associated with the 'Key' in the current session.
	 * @param String $key 
	 * @return String
	 * @throws SessionNotFoundException
	 */
	public function getData($key)
	{
		if($this -> session == null)
			throw new SessionNotFoundException("WARNING: No session is set for this user.");

		//check before retrieving data, if the session has expired.
		if($this->inactivityTimeout() || $this->expireTimeout())
		{
			echo "<BR>Session Timeout<BR>";
			$this->refreshSession();
		}

		$result = SQL("SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE `SESSION_ID` = ? and `KEY` = ?", array("{$this -> session}", $key));

		return $result;
	}
	
	
	/**
	 * To check if inactivity time has passed for this session.
	 * @return boolean
	 */
	public function inactivityTimeout()
	{
		if($this -> session == null)
			return TRUE;

		$currentActivityTime = time();	//get current time.

		$result = SQL("SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?", array("{$this -> session}"));
		$lastActivityTime = (int)$result[0]['LAST_ACTIVITY'];	//get the last time when the user was active.

		$difference = $currentActivityTime - $lastActivityTime;	//get difference betwen the current time and the last active time.

		if ($difference > Session::$inactivityMaxTime)	//if difference exceeds the inactivity time, destroy the session.
		{
			$this -> destroySession();
			return TRUE;
		}

		return FALSE;
	}
	
	
	/**
	 * To check if expiry time has passed for this session.
	 * @return boolean
	 */
	public function expireTimeout()
	{
		if($this -> session == null)
			return TRUE;

		$currentActivityTime = time();	//get current time.

		$result = SQL("SELECT `DATE_CREATED` FROM SESSION WHERE `SESSION_ID` = ?", array("{$this -> session}"));
		$lastActivityTime = (int)$result[0]['DATE_CREATED'];	//get the date when this session was created.

		$difference = $currentActivityTime - $lastActivityTime;	//get difference betwen the current time and the creation time.

		if ($difference > Session::$expireMaxTime)	//if difference exceeds the expiry time, destroy the session.
		{
			$this -> destroySession();
			return TRUE;
		}

		return FALSE;
	}
	
	
	/**
	 * To refresh the session ID of the current session. This will update the last time that the user was active and the session creation date to the current time. The essence is to make the session ID look like it was just created now.
	 * @return boolean
	 */
	public function refreshSession()
	{
		//If session is not set, then just create a new session.
		if ($this -> session == null)
		{
			$this -> newSession();
			return TRUE;
		}
		else	//If session is already set.
		{
			//check for session expiry.
			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout.!!!!<BR>";
				$this -> newSession();
				return TRUE;
			}

			$currentTime = time();

			//exchange the old session's creation date and the last activity time with the current time.
			SQL("UPDATE SESSION SET `DATE_CREATED` = ? , `LAST_ACTIVITY` = ? WHERE SESSION_ID = ?", array($currentTime, $currentTime, "{$this -> session}"));

			return TRUE;
		}
	}
	
	
	/**
	 * To destroy the current Session.
	 * @return boolean
	 * @throws SessionNotFoundException
	 */
	public function destroySession()
	{
		if($this -> session == null)
			throw new SessionNotFoundException("WARNING: No session is set for this user.");

		//delete all data associated with this session ID.
		SQL("DELETE FROM SESSION_DATA WHERE `SESSION_ID` = ?", array("{$this -> session}"));

		//delete this sessiom ID.
		SQL("DELETE FROM SESSION WHERE `SESSION_ID` = ?", array("{$this -> session}"));

		$this -> session = null;
		$this -> updateTotalNoOfSessions();	//update the total sessions because we just deleted one.

		return TRUE;
	}
	
	
	/**
	 * To destroy all the sessions associated with the current User.
	 * @return boolean
	 */
	public function destroyAllSessions()
	{
		$allSessions = $this->getAllSessions();	//get all sessions associated with this user.

		// For each of those sessions, delete data stored by those sessions and then delete the session IDs.
		foreach ($allSessions as $args)
		{
			$sess = $args['SESSION_ID'];

			SQL("DELETE FROM SESSION_DATA WHERE `SESSION_ID` = ?", array("{$sess}"));

			SQL("DELETE FROM SESSION WHERE `SESSION_ID` = ?", array("{$sess}"));
		}

		$this->session = null;
		$this -> updateTotalNoOfSessions();

		return TRUE;
	}
	
	
	/**
	 * To promote/demote a session. This essentially destroys the current session ID and issues a new session ID.
	 * @return boolean
	 * @throws SessionNotFoundException
	 */
	public function rollSession()
	{
		if($this -> session == null)
			throw new SessionNotFoundException("WARNING: No session is set for this user.");

		//check for session expiry.
		if($this->inactivityTimeout() || $this->expireTimeout())
		{
			echo "<BR>Session Timeout.<BR>";
			$this -> newSession();
			return TRUE;
		}

		//get all the data that is stored by this session.
		$result = SQL("SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE SESSION_ID = ?", array("{$this -> session}"));

		//destroy the current session.
		$this -> destroySession();

		//create a new session.
		$this -> newSession();

		//copy all the previous data to this new session.
		foreach( $result as $arg )
		{
			$this -> setData($arg['KEY'], $arg['VALUE']);
		}

		return TRUE;
	}
}

?>
