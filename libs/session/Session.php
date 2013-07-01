<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once (__DIR__ . '/../core/Rand.php');
require_once (__DIR__ . '/../core/Time.php');

/**
 * Parent Exception Class
 */
class SessionException extends \Exception {}

/**
 * Child Exception Classes
 */
class SessionNotFoundException extends SessionException {}		//Use of sessions before setting them.
class NoUserFoundException extends SessionException {}			//User not found to be associated with a session.
class DBHandlerForSessionNotSetException extends SessionException {}	//Database handler to handle SQL queries is not set.

class Session
{
	
	/**
	 * To hold the session ID.
	 * @var type String
	 */
	protected $session = null;
	
	
	/**
	 * To hold the user ID.
	 * @var type String
	 */
	protected $userID = null;	//for a session to present, there has to be a user. Without a user, a session cannot exist. So you have to create users in such a way that by the userID, the system can differentiate between a guest-user and a priviledged-user. Because you would need this distinction in RBAC.
	
	
	/**
	 * Database object to make SQL queries.
	 * @var type Database Object
	 */
	protected $handler = null;
	
	
	/**
	 * To hold the maximum time that is considered to be idle for user. After this period, the session must expire.
	 * @var type int
	 */
	public static $inactivityMaxTime = 1800;	//30 min.
	
	
	/**
	 * To hold the maximum time that is considered as session age. After this period, the session must expire.
	 * @var type int
	 */
	public static $expireMaxTime = 604800;	//1 week.
	
	
	/**
	 * Constructor to initialize a new session for some user.
	 * @param type $dbConn Database Object
	 * @param type $user String
	 * @throws DBHandlerForSessionNotSetException
	 */
	public function __construct($dbConn, $user)
	{
		$this -> handler = $dbConn;
		
		if ($this -> handler == null)
		{
			throw new DBHandlerForSessionNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			$this -> userID = $user;
			$this -> newSession();	//get a new session ID for the current user.
		}
	}
	
	
	/**
	 * To return the current session ID.
	 * @return type String
	 */
	public function getSessionID()
	{
		return $this -> session;
	}
	
	
	/**
	 * To return the current User ID.
	 * @return type String
	 */
	public function getUserID()
	{
		return $this -> userID;
	}
	
	
	/**
	 * To create a new Session ID for the current session.
	 * @return boolean
	 * @throws \phpsec\Exception
	 * @throws NoUserFoundException
	 */
	protected function newSession()
	{
		try
		{
			if($this->userID == null)
				throw new NoUserFoundException("<BR>ERROR: No User was found. Session needs a user to be present.<BR>");
			
			$this -> session = Rand :: generateRandom(32); //generate a new random string for the session ID of length 32.
			$time = Time::time();	//get the current time.

			$query = "INSERT INTO SESSION (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)";
			$args = array("{$this -> session}", $time, $time, "{$this -> userID}");

			$this -> handler -> SQL($query, $args);

			$this -> updateTotalNoOfSessions();	//update the total number of sessions, since we just created one.
			
			return TRUE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To update the total count of sessions for the current user.
	 * @return boolean
	 * @throws \phpsec\Exception
	 */
	protected function updateTotalNoOfSessions()
	{
		try
		{
			$result = $this -> getAllSessions();	//get all the session IDs for the current user.

			$totalCount = count($result);	//count the number of session IDs returned. Total number is the count of total number of sessions.

			$query = "UPDATE USER SET `TOTAL_SESSIONS` = ? WHERE USERID = ?";
			$args = array($totalCount, $this -> userID);
			$this -> handler -> SQL($query, $args);
			
			return TRUE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To get all session IDs for the current user.
	 * @return type StringArray
	 * @throws \phpsec\Exception
	 */
	public function getAllSessions()
	{
		try
		{
			$query = "SELECT SESSION_ID FROM SESSION WHERE USERID = ?";	//obtain all the session IDs that are associated with the current user.
			$args = array($this -> userID);
			$result = $this -> handler -> SQL($query, $args);

			return $result;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To set the data in the current session.
	 * @param type $key String
	 * @param type $value String
	 * @return boolean
	 * @throws \phpsec\Exception
	 * @throws SessionNotFoundException
	 */
	public function setData($key, $value)	
	{
		try
		{
			if($this -> session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");
			
			//check before setting data, if the session has expired.
			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout<BR>";
				$this->refreshSession();
			}
			
			//check if the key given by the user has already been set. If yes, then the value needs to be replaced and new record for key=>value is NOT needed.
			if ( count( $prevSession = $this -> getData($key) ) > 0 )
			{
				$query = "UPDATE SESSION_DATA SET `VALUE` = ? WHERE `KEY` = ? AND SESSION_ID = ?";
				$args = array($value, $key, "{$this -> session}");
				$this -> handler -> SQL($query, $args);
			}
			else //If the key is not found, then a new record of key=>value pair needs to be created.
			{
				$query = "INSERT INTO SESSION_DATA (`SESSION_ID`, `KEY`, `VALUE`) VALUES (?, ?, ?)";
				$args = array("{$this -> session}", $key, $value);
				$this -> handler -> SQL($query, $args);
			}
			
			return TRUE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To get the data associated with the 'Key' in the current session.
	 * @param type $key string
	 * @return type String
	 * @throws \phpsec\Exception
	 * @throws SessionNotFoundException
	 */
	public function getData($key)
	{
		try
		{
			if($this -> session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");
			
			//check before retrieving data, if the session has expired.
			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout<BR>";
				$this->refreshSession();
			}
			
			$query = "SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE `SESSION_ID` = ? and `KEY` = ?";
			$args = array("{$this -> session}", $key);
			$result = $this -> handler -> SQL($query, $args);

			return $result;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To check if inactivity time has passed for this session.
	 * @return boolean
	 * @throws \phpsec\Exception
	 */
	public function inactivityTimeout()
	{
		try
		{
			if($this -> session == null)
				return FALSE;
			
			$currentActivityTime = Time::time();	//get current time.

			$query = "SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?";
			$args = array("{$this -> session}");
			$result = $this -> handler -> SQL($query, $args);
			$lastActivityTime = (int)$result[0]['LAST_ACTIVITY'];	//get the last time when the user was active.

			$difference = $currentActivityTime - $lastActivityTime;	//get difference betwen the current time and the last active time.

			if ($difference > Session::$inactivityMaxTime)	//if difference exceeds the inactivity time, destroy the session.
			{
				$this -> destroySession();
				return TRUE;
			}

			return FALSE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To check if expiry time has passed for this session.
	 * @return boolean
	 * @throws \phpsec\Exception
	 */
	public function expireTimeout()
	{
		try
		{
			if($this -> session == null)
				return FALSE;
			
			$currentActivityTime = Time::time();	//get current time.

			$query = "SELECT `DATE_CREATED` FROM SESSION WHERE `SESSION_ID` = ?";
			$args = array("{$this -> session}");
			$result = $this -> handler -> SQL($query, $args);
			$lastActivityTime = (int)$result[0]['DATE_CREATED'];	//get the date when this session was created.

			$difference = $currentActivityTime - $lastActivityTime;	//get difference betwen the current time and the creation time.

			if ($difference > Session::$expireMaxTime)	//if difference exceeds the expiry time, destroy the session.
			{
				$this -> destroySession();
				return TRUE;
			}

			return FALSE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To refresh the session ID of the current session. This will update the last time that the user was active and the session creation date to the current time. The essence is to make the session ID look like it was just created now.
	 * @return boolean
	 * @throws \phpsec\Exception
	 */
	public function refreshSession()
	{
		//If session is not set, then just create a new session.
		if ($this -> session == null)
		{
			try
			{
				$this -> newSession();
				return TRUE;
			}
			catch(\Exception $e)
			{
				throw $e;	//probably the DB class will throw PDOExceptions
			}
		}
		else	//If session is already set.
		{
			try
			{
				//check for session expiry.
				if($this->inactivityTimeout() || $this->expireTimeout())
				{
					echo "<BR>Session Timeout.!!!!<BR>";
					$this -> newSession();
					return TRUE;
				}
				
				$currentTime = Time::time();

				//exchange the old session's creation date and the last activity time with the current time.
				$query = "UPDATE SESSION SET `DATE_CREATED` = ? , `LAST_ACTIVITY` = ? WHERE SESSION_ID = ?";
				$args = array($currentTime, $currentTime, "{$this -> session}");
				$this -> handler -> SQL($query, $args);
				
				return TRUE;
			}
			catch(\Exception $e)
			{
				throw $e;	//probably the DB class will throw PDOExceptions
			}
		}
	}
	
	
	/**
	 * To destroy the current Session.
	 * @return boolean
	 * @throws \phpsec\Exception
	 * @throws SessionNotFoundException
	 */
	public function destroySession()
	{
		try
		{
			if($this -> session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");
			
			//delete all data associated with this session ID.
			$query = "DELETE FROM SESSION_DATA WHERE `SESSION_ID` = ?";
			$args = array("{$this -> session}");
			$this -> handler -> SQL($query, $args);
			
			//delete this sessiom ID.
			$query = "DELETE FROM SESSION WHERE `SESSION_ID` = ?";
			$args = array("{$this -> session}");
			$this -> handler -> SQL($query, $args);
			
			$this -> session = null;
			$this -> updateTotalNoOfSessions();	//update the total sessions because we just deleted one.
			
			return TRUE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To destroy all the sessions associated with the current User.
	 * @return boolean
	 * @throws \phpsec\Exception
	 */
	public function destroyAllSessions()
	{
		try
		{
			$allSessions = $this->getAllSessions();	//get all sessions associated with this user.

			// For each of those sessions, delete data stored by those sessions and then delete the session IDs.
			foreach ($allSessions as $args)
			{
				$sess = $args['SESSION_ID'];
				
				$query = "DELETE FROM SESSION_DATA WHERE `SESSION_ID` = ?";
				$args = array("{$sess}");
				$this -> handler -> SQL($query, $args);

				$query = "DELETE FROM SESSION WHERE `SESSION_ID` = ?";
				$args = array("{$sess}");
				$this -> handler -> SQL($query, $args);
			}
			
			$this->session = null;
			$this -> updateTotalNoOfSessions();
			
			return TRUE;
		}
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
	
	
	/**
	 * To promote/demote a session. This essentially destroys the current session ID and issues a new session ID.
	 * @return boolean
	 * @throws \phpsec\Exception
	 * @throws SessionNotFoundException
	 */
	public function rollSession()
	{
		try
		{
			if($this -> session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");

			//check for session expiry.
			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout.<BR>";
				$this -> newSession();
				return TRUE;
			}
			
			//get all the data that is stored by this session.
			$query = "SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE SESSION_ID = ?";
			$args = array("{$this -> session}");
			$result = $this -> handler -> SQL($query, $args);
			
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
		catch(\Exception $e)
		{
			throw $e;	//probably the DB class will throw PDOExceptions
		}
	}
}

?>
