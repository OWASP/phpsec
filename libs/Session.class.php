<?php

require('DB.class.php');
require('Rand.class.php');
require('User.class.php');

class Session
{
	private $_session = null;
	private $_userID = null;
	private $_handler = null;
	private $_inactivityMaxTime = 1800;	//30 min.
	private $_expireMaxTime = 604800;	//1 week.
	
	public function __construct($user, $adapter, $dbName, $username, $password, $host = "localhost")
	{
		$this -> _handler = new DB();
		
		if (! $this -> _handler ->setDB($adapter, $dbName, $username, $password, $host))
		{
			throw new Exception("DB is not set properly.");
		}
		else
		{
			$this -> _setUserID($user);
			$this -> _newSession();
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
	
	private function _sessionGenerator()
	{
		return Rand :: generateRandom();
	}
	
	private function _newSession()
	{
		if ($this -> _handler != null)
		{
			try
			{
				$this -> _session = $this -> _sessionGenerator();
				$time = time();
				$query = "INSERT INTO SESSION (`SESSION_ID`, `DATE_CREATED`, `LAST_ACTIVITY`, `USERID`) VALUES (?, ?, ?, ?)";
				$args = array("{$this -> _session}", $time, $time, "{$this -> _userID}");
				$count = $this -> _handler -> prepare($query, $args);

				if ($count != 1)
					throw new Exception ("Unable to insert data in DB.");
				else
				{
					$this -> _updateTotalNoOfSessions();
				}
			}
			catch(Exception $e)
			{
				throw new Exception($e -> getMessage());
			}
			
		}
		else
			throw new Exception("DB is not set properly.");
	}
	
	public function getAllSessions()
	{
		$query = "SELECT SESSION_ID FROM SESSION WHERE USERID = ?";
		$args = array($this -> _userID);
		$result = $this -> _handler -> prepare($query, $args);
		
		return $result;
	}
	
	private function _updateTotalNoOfSessions()
	{
		$result = $this -> getAllSessions();
		
		$totalCount = count($result);
		
		$query = "UPDATE USER SET `TOTAL_SESSIONS` = ? WHERE USERID = ?";
		$args = array($totalCount, $this -> _userID);
		$count = $this -> _handler -> prepare($query, $args);
		
		if ($count != 1)
			throw new Exception ("Unable to update total no. of sessions.");
	}
	
	private function _setUserID($user)
	{
		if ($this -> _handler != null)
		{
			try
			{
				$this -> _userID = $user -> getUserID();
				return $this -> _userID;
			}
			catch(Exception $e)
			{
				throw new Exception($e -> getMessage());
			}
		}
		else
			throw new Exception("DB is not set properly.");
	}
	
	public function destroySession()
	{
		if ($this -> _handler != null)
		{
			$query = "DELETE FROM SESSION_DATA WHERE `SESSION_ID` = ?";
			$args = array("{$this -> _session}");
			$count = $this -> _handler -> prepare($query, $args);

			if ($count > 0)
			{
				$query = "DELETE FROM SESSION WHERE `SESSION_ID` = ?";
				$args = array("{$this -> _session}");
				$count = $this -> _handler -> prepare($query, $args);

				if ($count != 1)
					throw new Exception ("Session ID not deleted.");
				else
					$this -> _session = null;
					$this -> _updateTotalNoOfSessions();
			}
			else
			{
				throw new Exception("Session data and Session ID, both not deleted.");
			}
		}
		else
		{
			$this -> _session = null;
			throw new Exception("DB is not set properly.");
		}
	}
	
	/**
	 * Check user input.
	 * @param type $key
	 * @param type $value
	 * @return boolean
	 */
	public function setData($key, $value)	
	{
		if ($this -> _handler != null)
		{
			if ($this -> _session != null)
			{
				if ( count( $prevSession = $this -> getData($key) ) > 0 )
				{
					$query = "UPDATE SESSION_DATA SET `VALUE` = ? WHERE `KEY` = ? AND SESSION_ID = ?";
					$args = array($value, $key, "{$this -> session}");
					$count = $this -> _handler -> prepare($query, $args);
					
					if ($count != 1)
						throw new Exception ("Unable to update the value of key.");
				}
				else
				{
					$query = "INSERT INTO SESSION_DATA (`SESSION_ID`, `KEY`, `VALUE`) VALUES (?, ?, ?)";
					$args = array("{$this -> _session}", $key, $value);
					$count = $this -> _handler -> prepare($query, $args);

					if ($count != 1)
						throw new Exception ("Unable to insert data in DB.");
				}
			}
			else
				throw new Exception("Session is not set properly.");
		}
		else
			throw new Exception("DB is not set properly.");
	}
	
	/**
	 * Check user input
	 * @param type $key
	 * @return boolean
	 */
	public function getData($key)
	{
		if ($this -> _handler != null)
		{
			if ($this -> _session != null)
			{
				$query = "SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE `SESSION_ID` = ? and `KEY` = ?";
				$args = array("{$this -> session}", $key);
				$result = $this -> handler -> prepare($query, $args);

				return $result;
			}
			else
				throw new Exception("Session is not set properly.");
		}
		else
			throw new Exception("DB is not set properly.");
	}
	
	public function setInactivityTime($seconds)
	{
		if (gettype($seconds) != "integer" || $seconds <= 0)
		{
			throw new Exception("Corrupt value received in argument.");
		}
		else
			$this -> _inactivityMaxTime = $seconds;
	}
	
	public function getInactivityTime()
	{
		return $this -> _inactivityMaxTime;
	}
	
	public function inactivityTimeout()
	{
		if ($this -> _handler != null)
		{
			if ($this -> _session != null)
			{
				$currentActivityTime = time();
			
				$query = "SELECT `LAST_ACTIVITY` FROM SESSION WHERE `SESSION_ID` = ?";
				$args = array("{$this -> _session}");
				$result = $this -> _handler -> prepare($query, $args);
				$lastActivityTime = (int)$result[0]['LAST_ACTIVITY'];

				$difference = $currentActivityTime - $lastActivityTime;

				if ($difference > $this -> getInactivityTime())
				{
					try
					{
						$this -> destroySession();
					}
					catch(Exception $e)
					{
						throw new Exception($e -> getMessage());
					}
				}

				return FALSE;
			}
			else
				throw new Exception("Session is not set properly.");
		}
		else
			throw new Exception("DB is not set properly.");
	}
	
	public function setExpireTime($seconds)
	{
		if (gettype($seconds) != "integer" || $seconds <= 0)
		{
			throw new Exception("Corrupt value received in argument.");
		}
		else
			$this -> _expireMaxTime = $seconds;
	}
	
	public function getExpireTime()
	{
		return $this -> _expireMaxTime;
	}
	
	public function expireTimeout()
	{
		if ($this -> _handler != null)
		{
			if ($this -> _session != null)
			{
				$currentActivityTime = time();
			
				$query = "SELECT `DATE_CREATED` FROM SESSION WHERE `SESSION_ID` = ?";
				$args = array("{$this -> _session}");
				$result = $this -> _handler -> prepare($query, $args);
				$lastActivityTime = (int)$result[0]['DATE_CREATED'];

				$difference = $currentActivityTime - $lastActivityTime;

				if ($difference > $this -> getExpireTime())
				{
					try
					{
						$this -> destroySession();
					}
					catch(Exception $e)
					{
						throw new Exception($e -> getMessage());
					}
				}

				return FALSE;
			}
			else
				throw new Exception("Session is not set properly.");
		}
		else
			throw new Exception("DB is not set properly.");
	}
	
	public function refreshSession()
	{
		if ($this -> _session == null)
		{
			try
			{
				return $this -> _newSession();
			}
			catch(Exception $e)
			{
				throw new Exception($e -> getMessage());
			}
		}
		else
		{
			if ($this -> _handler != null)
			{
				$currentTime = time();

				$query = "UPDATE SESSION SET `DATE_CREATED` = ? , `LAST_ACTIVITY` = ? WHERE SESSION_ID = ?";
				$args = array($currentTime, $currentTime, "{$this -> _session}");
				$count = $this -> _handler -> prepare($query, $args);

				if ($count != 1)
					throw new Exception ("Unable to update session in DB.");
			}
			else
				throw new Exception("DB is not set properly.");
		}
	}
	
	/**
	 * While rolling the session, what to do with the existing sessions and their data. Should I keep the data or destroy them.
	 * @return boolean
	 */
	public function rollSession()
	{
		if ($this -> _handler != null)
		{
			if ($this -> _session == null)
			{
				try
				{
					return $this -> _newSession();
				}
				catch(Exception $e)
				{
					throw new Exception($e -> getMessage());
				}
			}
			else
			{
				$query = "SELECT `KEY`, `VALUE` FROM SESSION_DATA WHERE SESSION_ID = ?";
				$args = array("{$this -> _session}");
				$result = $this -> _handler -> prepare($query, $args);
				
				$this -> destroySession();
				$this -> rollSession();
				
				foreach( $result as $arg )
				{
					$this -> setData($arg['KEY'], $arg['VALUE']);
				}
			}
		}
		else
			throw new Exception("DB is not set properly.");
	}
}

?>