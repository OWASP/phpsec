<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Rand.class.php');
require_once (__DIR__ . '/../core/Time.class.php');

class AdvPasswordManagement extends \Exception {}

class DBHandlerForAdvPasswordNotSetException extends AdvPasswordManagement {}
class CorruptUserException extends AdvPasswordManagement {}



class AdvancedPasswordManagement
{
	private $_handler = null;
	private $_user = null;
	
	private static $_tempPassExpiryTime = 900;
	
	public function __construct($dbConn, $user)
	{
		$this->_handler = $dbConn;
		
		if ($this->_handler == null)
		{
			throw new DBHandlerForAdvPasswordNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			try
			{
				$this->_user = $user;

				$query = "INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `LAST_RESET`, `USERID`) VALUES (?, ?, ?, ?, ?)";
				$args = array("UNSET", 1, 0, 0, $this->_user->getUserID());
				$count = $this->_handler -> SQL($query, $args);

				if ($count == 0)
					throw new CorruptUserException("<BR>ERROR: Unable to insert in DB. Either User already exists in table or invalid username.<BR>");
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public static function setTempPassExpiryTime($time)
	{
		if( ( gettype($time) != "integer" ) )
			throw new \Exception("<BR>ERROR: Integer is required. " . gettype($time) . " was found.<BR>");
		
		AdvancedPasswordManagement::$_tempPassExpiryTime = $time;
	}
	
	public static function getTempPassExpiryTime()
	{
		return AdvancedPasswordManagement::$_tempPassExpiryTime;
	}
	
	public static function checkIfTimeExpired($givenTime)
	{
		$currentTime = Time::time();
		
		if ( ($currentTime - $givenTime)  > AdvancedPasswordManagement::$_tempPassExpiryTime)
			return TRUE;
		else
			return FALSE;
	}
	
	public function forgotPassword($tempPass = "")
	{
		if ($tempPass == "")
		{
			try
			{
				$tempPass = hash("sha512", Rand::generateRandom(64));
				$time = Time::time();

				$query = "UPDATE PASSWORD SET `TEMP_PASS` = ?, `USE_FLAG` = ?, `TEMP_TIME` = ? WHERE USERID = ?";
				$args = array($tempPass, 0, $time, $this->_user->getUserID());
				$count = $this->_handler -> SQL($query, $args);
				
				return TRUE;
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
		else
		{
			try
			{
				$query = "SELECT `TEMP_PASS`, `USE_FLAG`, `TEMP_TIME` FROM PASSWORD WHERE `USERID` = ?";
				$args = array($this->_user->getUserID());
				$result = $this->_handler -> SQL($query, $args);
				
				if (count($result) < 1)
					throw new CorruptUserException("<BR>ERROR: Unable to fetch data. Username might be wrong.</BR>");
				
				if ( ($result[0]['USE_FLAG'] == 0) && (!AdvancedPasswordManagement::checkIfTimeExpired($result[0]['TEMP_TIME'])))
				{	
					if ( $result[0]['TEMP_PASS'] != $tempPass )
						return FALSE;
					
					$query = "DELETE FROM PASSWORD WHERE USERID = ?";
					$args = array($this->_user->getUserID());
					$count = $this->_handler -> SQL($query, $args);
					
					return TRUE;
				}
				else
				{
					$query = "DELETE FROM PASSWORD WHERE USERID = ?";
					$args = array($this->_user->getUserID());
					$count = $this->_handler -> SQL($query, $args);
					
					return FALSE;
				}
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
}

?>