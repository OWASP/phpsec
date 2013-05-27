<?php
namespace phpsec;

$presentDirectory = getcwd();
chdir(  dirname(__FILE__) );

require_once ('../core/Rand.class.php');
require_once ('../core/Time.class.php');
require_once ('../core/Exception.class.php');

chdir($presentDirectory);

class Session
{
	private $_session = null;
	private $_userID = null;	//for a session to present, there has to be a user. Without a user, a session cannot exist. So you have to create users in such a way that by the userID, the system can differentiate between a guest-user and a priviledged-user. Because you would need this distinction in RBAC.
	private $_handler = null;
	
	private static $_inactivityMaxTime = 1800;	//30 min.
	private static $_expireMaxTime = 604800;	//1 week.
	
	public function __construct($user, $dbConn)
	{
		$this -> _handler = $dbConn;
		
		if ($this -> _handler == null)
		{
			throw new DBConnectionNotFoundException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			$this -> _userID = $this ->_getUserID($user);
			$this -> _newSession();
		}
	}
	
	public function getSessionID()
	{
		return $this -> _session;
	}
	
	private function _getUserID($user)
	{
		return $user->getUserID();
	}
	
	private function _newSession()
	{
		try
		{
			if($this->_userID == null)
				throw new NoUserFoundException("<BR>ERROR: No User was found. Session needs a user to be present.<BR>");
			
			$this -> _session = $this -> _sessionGenerator();
			$time = Time::time();

			$query = "INSERT INTO SESSION (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)";
			$args = array("{$this -> _session}", $time, $time, "{$this -> _userID}");

			$count = $this -> _handler -> SQL($query, $args);

			if ($count == 0)
				throw new DBQueryNotExecutedError("<BR>ERROR: Unable to insert new Session data in DB.<BR>");
			else
			{
				$this -> _updateTotalNoOfSessions();
			}
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	private function _sessionGenerator()
	{
		return Rand :: generateRandom();
	}
	
	private function _updateTotalNoOfSessions()
	{
		try
		{
			$result = $this -> getAllSessions();

			$totalCount = count($result);

			$query = "UPDATE USER SET `TOTAL_SESSIONS` = ? WHERE USERID = ?";
			$args = array($totalCount, $this -> _userID);
			$count = $this -> _handler -> SQL($query, $args);
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	public function getAllSessions()
	{
		try
		{
			$query = "SELECT SESSION_ID FROM SESSION WHERE USERID = ?";
			$args = array($this -> _userID);
			$result = $this -> _handler -> SQL($query, $args);

			return $result;
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	public function checkHTTPS()
	{
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
		{
			return TRUE;
		}
		else
			return FALSE;
	}
	
	/**
	 * Check user input.
	 * @param type $key
	 * @param type $value
	 * @return boolean
	 */
	public function setData($key, $value)	
	{
		try
		{
			if($this -> _session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");
			
			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout<BR>";
				$this->refreshSession();
			}
			
			if ( count( $prevSession = $this -> getData($key) ) > 0 )
			{
				$query = "UPDATE SESSION_DATA SET `VALUE` = ? WHERE `KEY` = ? AND SESSION_ID = ?";
				$args = array($value, $key, "{$this -> _session}");
				$count = $this -> _handler -> SQL($query, $args);
			}
			else
			{
				$query = "INSERT INTO SESSION_DATA (`SESSION_ID`, `KEY`, `VALUE`) VALUES (?, ?, ?)";
				$args = array("{$this -> _session}", $key, $value);
				$count = $this -> _handler -> SQL($query, $args);

				if ($count == 0)
					throw new DBQueryNotExecutedError("<BR>ERROR: Unable to insert the new {$key}/{$value} pair in the DB.<BR>");
			}
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	/**
	 * Check user input
	 * @param type $key
	 * @return boolean
	 */
	public function getData($key)
	{
		try
		{
			if($this -> _session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");
			
			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout<BR>";
				$this->refreshSession();
			}
			
			$query = "SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE `SESSION_ID` = ? and `KEY` = ?";
			$args = array("{$this -> _session}", $key);
			$result = $this -> _handler -> SQL($query, $args);

			return $result;
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	public static function setInactivityTime($seconds)
	{
		if (gettype($seconds) != "integer" || $seconds <= 0)
		{
			throw new IntegerNotFoundException("<BR>ERROR: Integer is required to set \"Inactivity Time\". " . gettype($difference) . " was found.<BR>");
		}
		else
			Session::$_inactivityMaxTime = $seconds;
	}
	
	public static function getInactivityTime()
	{
		return Session::$_inactivityMaxTime;
	}
	
	public function inactivityTimeout()
	{
		try
		{
			if($this -> _session == null)
				return FALSE;
			
			$currentActivityTime = Time::time();

			$query = "SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?";
			$args = array("{$this -> _session}");
			$result = $this -> _handler -> SQL($query, $args);
			$lastActivityTime = (int)$result[0]['LAST_ACTIVITY'];

			$difference = $currentActivityTime - $lastActivityTime;

			if ($difference > Session::getInactivityTime())
			{
				$this -> destroySession();
				return TRUE;
			}

			return FALSE;
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	public static function setExpireTime($seconds)
	{
		if (gettype($seconds) != "integer" || $seconds <= 0)
		{
			throw new IntegerNotFoundException("<BR>ERROR: Integer is required to set \"Expiry Time\". " . gettype($difference) . " was found.<BR>");
		}
		else
			Session::$_expireMaxTime = $seconds;
	}
	
	public static function getExpireTime()
	{
		return Session::$_expireMaxTime;
	}
	
	public function expireTimeout()
	{
		try
		{
			if($this -> _session == null)
				return FALSE;
			
			$currentActivityTime = Time::time();

			$query = "SELECT `DATE_CREATED` FROM SESSION WHERE `SESSION_ID` = ?";
			$args = array("{$this -> _session}");
			$result = $this -> _handler -> SQL($query, $args);
			$lastActivityTime = (int)$result[0]['DATE_CREATED'];

			$difference = $currentActivityTime - $lastActivityTime;

			if ($difference > Session::getExpireTime())
			{
				$this -> destroySession();
				return TRUE;
			}

			return FALSE;
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	public function refreshSession()
	{
		if ($this -> _session == null)
		{
			try
			{
				$this -> _newSession();
				return TRUE;
			}
			catch(DBQueryNotExecutedError $e)
			{
				throw new DBQueryNotExecutedError($e->getMessage());
			}
			catch(\Exception $e)
			{
				throw new \Exception($e -> getMessage());
			}
		}
		else
		{
			try
			{
				if($this->inactivityTimeout() || $this->expireTimeout())
				{
					echo "<BR>Session Timeout.!!!!<BR>";
					$this -> _newSession();
					return TRUE;
				}
				
				$currentTime = Time::time();

				$query = "UPDATE SESSION SET `DATE_CREATED` = ? , `LAST_ACTIVITY` = ? WHERE SESSION_ID = ?";
				$args = array($currentTime, $currentTime, "{$this -> _session}");
				$count = $this -> _handler -> SQL($query, $args);
				
				return TRUE;
			}
			catch(\Exception $e)
			{
				throw new \Exception($e -> getMessage());
			}
		}
	}
	
	public function destroySession()
	{
		try
		{
			if($this -> _session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");
			
			$query = "DELETE FROM SESSION_DATA WHERE `SESSION_ID` = ?";
			$args = array("{$this -> _session}");
			$count = $this -> _handler -> SQL($query, $args);
			
			$query = "DELETE FROM SESSION WHERE `SESSION_ID` = ?";
			$args = array("{$this -> _session}");
			$count = $this -> _handler -> SQL($query, $args);

			if ($count == 0)
				throw new DBQueryNotExecutedError("<BR>WARNING: Session ID not deleted for this session while destroying session.<BR>");
			else
			{
				$this -> _session = null;
				$this -> _updateTotalNoOfSessions();
			}
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
	
	public function rollSession()
	{
		try
		{
			if($this -> _session == null)
				throw new SessionNotFoundException("<BR>WARNING: No session is set for this user.<BR>");

			if($this->inactivityTimeout() || $this->expireTimeout())
			{
				echo "<BR>Session Timeout.<BR>";
				$this -> _newSession();
				return TRUE;
			}
			
			$query = "SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE SESSION_ID = ?";
			$args = array("{$this -> _session}");
			$result = $this -> _handler -> SQL($query, $args);

			$this -> destroySession();
			$this -> _newSession();

			foreach( $result as $arg )
			{
				$this -> setData($arg['KEY'], $arg['VALUE']);
			}
			
			return TRUE;
		}
		catch(SessionNotFoundException $e)
		{
			throw new SessionNotFoundException($e->getMessage());
		}
		catch(DBQueryNotExecutedError $e)
		{
			throw new DBQueryNotExecutedError($e->getMessage());
		}
		catch(\Exception $e)
		{
			throw new \Exception($e -> getMessage());	//probably the DB class will throw PDOExceptions
		}
	}
}

?>