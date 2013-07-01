<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Rand.php');
require_once (__DIR__ . '/../core/Time.php');
require_once (__DIR__ . '/../auth/User.php');

class BruteForceAttackDetectedException extends WrongPasswordException {}

class AdvancedPasswordManagement extends User
{
	private $_userObj = null;
	
	private static $_tempPassExpiryTime = 900;	//15 min
	private static $_bruteForceLockAttempts = 5;	//This tells how many attemps must be considered before brute-force lock.
	private static $_bruteForceLockTimePeriod = 5;	//5 SEC  - This defines the time-period after which next login attempt must be carried out. E.g if the time is 5 sec, then time-period between two login attempts must minimum be 5 sec, otherwise it will be considered brite-force attack.
	private static $_automaticLoginTimePeriod = 604800;	//1 week - This defines the "remember me" time. So within one week, if the user does not logs out, he can get into the system without providing their credetials.
	
	public function __construct($dbConn, $user, $pass, $bruteLock = false)
	{
		try
		{
			$this->_userObj = User::existingUserObject($dbConn, $user, $pass);
		}
		catch(\phpsec\WrongPasswordException $e)
		{
			if ($bruteLock == true)
			{
				$bruteFound = false;
				try
				{
					$bruteFound = $this->isBruteForce($dbConn, $user);
				}
				catch(\Exception $exc)
				{
					throw $exc;
				}
				
				if ($bruteFound)
					throw new BruteForceAttackDetectedException($e->getMessage ( ) . "<BR>" . "<BR>WARNING: Brute Force Attack Detected. We Recommend you use captcha.<BR>");
			}
			else
				throw $e;
		}
		catch(\Exception $e)
		{
			throw $e;
		}
		
		try
		{
			$query = "INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT`, `USERID`) VALUES (?, ?, ?, ?, ?, ?)";
			$args = array(Rand::generateRandom(10), 1, 0, 0, Time::time(), $user);
			$count = $dbConn-> SQL($query, $args);
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	private function isBruteForce($dbConn, $user)
	{
		try
		{
			$currentTime = Time::time();
			
			$query = "SELECT `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT` FROM PASSWORD WHERE USERID = ?";
			$args = array($user);
			$result = $dbConn-> SQL($query, $args);
			
			if (count($result) < 1)
			{
				$query = "INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT`, `USERID`) VALUES (?, ?, ?, ?, ?, ?)";
				$args = array(Rand::generateRandom(10), 1, 0, 1, Time::time(), $user);
				$count = $dbConn-> SQL($query, $args);
				
				return FALSE;
			}
			else
			{
				if ( ($currentTime - $result[0]['LAST_LOGIN_ATTEMPT']) <= AdvancedPasswordManagement::$_bruteForceLockTimePeriod )
				{
					if ($result[0]['TOTAL_LOGIN_ATTEMPTS'] >= AdvancedPasswordManagement::$_bruteForceLockAttempts)
					{
						$query = "UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?";
						$args = array($currentTime, $user);
						$count = $dbConn-> SQL($query, $args);

						return TRUE;
					}
					else
					{
						$query = "UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?";
						$args = array($currentTime, $user);
						$count = $dbConn-> SQL($query, $args);

						return FALSE;
					}
				}
				else
				{
					$query = "UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?";
					$args = array(1, $currentTime, $user);
					$count = $dbConn-> SQL($query, $args);

					return FALSE;
				}
			}
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	public static function setBruteForceLockAttempts($no)
	{
		if( ( gettype($time) != "integer" ) )
			throw new \Exception("<BR>ERROR: Integer is required. " . gettype($time) . " was found.<BR>");
		
		AdvancedPasswordManagement::$_bruteForceLockAttempts = $no;
	}
	
	public static function getBruteForceLockAttempts()
	{
		return AdvancedPasswordManagement::$_bruteForceLockAttempts;
	}
	
	public static function setBruteForceLockTimePeriod($time)
	{
		if( ( gettype($time) != "integer" ) )
			throw new \Exception("<BR>ERROR: Integer is required. " . gettype($time) . " was found.<BR>");
		
		AdvancedPasswordManagement::$_bruteForceLockTimePeriod = $time;
	}
	
	public static function getBruteForceLockTimePeriod()
	{
		return AdvancedPasswordManagement::$_bruteForceLockTimePeriod;
	}
	
	public static function setAutomaticLoginTimePeriod($time)
	{
		if( ( gettype($time) != "integer" ) )
			throw new \Exception("<BR>ERROR: Integer is required. " . gettype($time) . " was found.<BR>");
		
		AdvancedPasswordManagement::$_automaticLoginTimePeriod = $time;
	}
	
	public static function getAutomaticLoginTimePeriod()
	{
		return AdvancedPasswordManagement::$_automaticLoginTimePeriod;
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
	
	public function checkIfTempPassExpired()
	{
		$query = "SELECT `TEMP_TIME` FROM PASSWORD WHERE `USERID` = ?";
		$args = array($this->_userObj->getUserID());
		$result = $this->_userObj->_handler-> SQL($query, $args);
			
		$currentTime = Time::time();
		
		if ( ($currentTime - $result[0]['TEMP_TIME'])  >= AdvancedPasswordManagement::$_tempPassExpiryTime)
			return TRUE;
		else
			return FALSE;
	}
	
	private static function checkIfTimeExpired($givenTime)
	{
		$currentTime = Time::time();
		
		if ( ($currentTime - $givenTime)  >= AdvancedPasswordManagement::$_tempPassExpiryTime)
			return TRUE;
		else
			return FALSE;
	}
	
	public function tempPassword($tempPass = "")
	{
		if ($tempPass == "")
		{
			try
			{
				$tempPass = hash("sha512", Rand::generateRandom(64));
				$time = Time::time();

				$query = "UPDATE PASSWORD SET `TEMP_PASS` = ?, `USE_FLAG` = ?, `TEMP_TIME` = ? WHERE USERID = ?";
				$args = array($tempPass, 0, $time, $this->_userObj->getUserID());
				$count = $this->_userObj->_handler-> SQL($query, $args);
				
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
				$args = array($this->_userObj->getUserID());
				$result = $this->_userObj->_handler-> SQL($query, $args);
				
				if ( ($result[0]['USE_FLAG'] == 0) && (!AdvancedPasswordManagement::checkIfTimeExpired($result[0]['TEMP_TIME'])))
				{	
					if ( $result[0]['TEMP_PASS'] != $tempPass )
						return FALSE;
					
					$query = "UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?";
					$args = array(Rand::generateRandom(10), 1, 0, $this->_userObj->getUserID());
					$count = $this->_userObj->_handler-> SQL($query, $args);
					
					return TRUE;
				}
				else
				{
					$query = "UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?";
					$args = array(Rand::generateRandom(10), 1, 0, $this->_userObj->getUserID());
					$count = $this->_userObj->_handler-> SQL($query, $args);
					
					return FALSE;
				}
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public function rememberMe($secure = TRUE, $httpOnly = TRUE)
	{
		if ( !isset($_COOKIE['AUTHID']) )
		{
			try
			{
				$newID = hash("sha512", Rand::generateRandom(64));
				
				$query = "INSERT INTO AUTH_STORAGE (`AUTH_ID`, `DATE_CREATED`, `USERID`) VALUES (?, ?, ?)";
				$args = array($newID, Time::time(), $this->_userObj->getUserID());
				$count = $this->_userObj->_handler-> SQL($query, $args);
				
				if ($secure && $httpOnly)
					\setcookie("AUTHID", $newID, Time::time ( ) + 29999999, null, null, TRUE, TRUE);	//keep cookie for unlimited time because it doesn't matter. The time that cookie will be present in client's system will be determined from the $_automaticLoginTimePeriod variable. Once this time has passed, the cookie will be cancelled from the server end.
				elseif (!$secure && !$httpOnly)
					\setcookie("AUTHID", $newID, Time::time ( ) + 299999999, null, null, FALSE, FALSE);
				elseif ($secure && !$httpOnly)
					\setcookie("AUTHID", $newID, Time::time ( ) + 299999999, null, null, TRUE, FALSE);
				elseif (!$secure && $httpOnly)
					\setcookie("AUTHID", $newID, Time::time ( ) + 299999999, null, null, FALSE, TRUE);
				
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
				$query = "SELECT `AUTH_ID`, `DATE_CREATED` FROM `AUTH_STORAGE` WHERE `USERID` = ?";
				$args = array($this->_userObj->getUserID());
				$result = $this->_userObj->_handler-> SQL($query, $args);
				
				foreach ($result as $auth)
				{
					if ($auth['AUTH_ID'] == $_COOKIE['AUTHID'])
					{
						$currentTime = Time::time();

						if ( ($currentTime - $auth['DATE_CREATED']) >= AdvancedPasswordManagement::$_automaticLoginTimePeriod)
						{
							$query = "DELETE FROM `AUTH_STORAGE` WHERE USERID = ? AND `AUTH_ID` = ?";
							$args = array($this->_userObj->getUserID(), $_COOKIE['AUTHID']);
							$count = $this->_userObj->_handler-> SQL($query, $args);

							setcookie("AUTHID", "");

							return FALSE;
						}
						else
							return TRUE;
					}
				}
				
				return FALSE;
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
}

?>
