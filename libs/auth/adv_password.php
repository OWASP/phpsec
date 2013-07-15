<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once (__DIR__ . '/../core/random.php');
require_once (__DIR__ . '/../core/time.php');
require_once (__DIR__ . '/user.php');


/**
 * Child Exceptions.
 */
class BruteForceAttackDetectedException extends WrongPasswordException {}	//a subclass of WrongPasswordException, this exception will arise when it detects brute-force attacks.



class AdvancedPasswordManagement
{
	
	/**
	 * To keep the object of current user.
	 * @var \phpsec\User
	 */
	protected $userID = null;
	
	
	/**
	 * To keep the time after which the temp. password generated for the current user must expire. To expire means that a new temporary password or other means of authenticatin must be used for login. This temporary password will die after the specified period.
	 * @var int
	 */
	public static $tempPassExpiryTime = 900;	//15 min
	
	
	/**
	 * It denotes the # of maximum attempts for login using the password. If this limit exceeds and this happens within a very short amount of time, then it is considered as a brute force attack.
	 * @var int
	 */
	public static $bruteForceLockAttempts = 5;	//This tells how many attemps must be considered before brute-force lock.
	
	
	/**
	 * It denotes the amount of time in seconds between which no two wrong passwords must be entered. If this happens, then it is considered that a bot is trying to hack the account using brute-force means.
	 * @var int
	 */
	public static $bruteForceLockTimePeriod = 5;	//5 SEC  - This defines the time-period after which next login attempt must be carried out. E.g if the time is 5 sec, then time-period between two login attempts must minimum be 5 sec, otherwise it will be considered brute-force attack.
	
	
	
	/**
	 * Constructor for the AdvancedPasswordManagement Class.
	 * @param DatabaseObject $dbConn
	 * @param String $user		//The ID of the user.
	 * @param String $pass		//The password of the user.
	 * @param boolean $bruteLock	//If brute force detection is enabled.
	 * @throws BruteForceAttackDetectedException
	 * @throws \phpsec\WrongPasswordException
	 * @throws \phpsec\UserException
	 */
	public function __construct($user, $pass, $bruteLock = false)
	{
		try
		{
			$this->userID = $user;
			
			$userObj = User::existingUserObject($user, $pass);
		}
		catch(\phpsec\WrongPasswordException $e)
		{
			if ($bruteLock == true)
			{
				$bruteFound = false;
				
				$bruteFound = $this->isBruteForce($user);
				
				if ($bruteFound)
					throw new BruteForceAttackDetectedException($e->getMessage ( ) . "<BR>" . "<BR>WARNING: Brute Force Attack Detected. We Recommend you use captcha.<BR>");
			}
			else
				throw $e;
		}
		catch(  \phpsec\UserException $e)
		{
			throw $e;
		}
		
		SQL("INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT`, `USERID`) VALUES (?, ?, ?, ?, ?, ?)", array(  randstr(10), 1, 0, 0, time(), $user));
	}
	
	
	
	/**
	 * Function to detect brute-force attacks.
	 * @param DatabaseObject $dbConn
	 * @param String $user
	 * @return boolean
	 */
	protected function isBruteForce($user)
	{
		$currentTime = time();
			
		$result = SQL("SELECT `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT` FROM PASSWORD WHERE USERID = ?", array($user));

		if (count($result) < 1)
		{
			SQL("INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT`, `USERID`) VALUES (?, ?, ?, ?, ?, ?)", array(  randstr(10), 1, 0, 1, time(), $user));

			return FALSE;
		}
		else
		{
			if ( ($currentTime - $result[0]['LAST_LOGIN_ATTEMPT']) <= AdvancedPasswordManagement::$bruteForceLockTimePeriod )
			{
				if ($result[0]['TOTAL_LOGIN_ATTEMPTS'] >= AdvancedPasswordManagement::$bruteForceLockAttempts)
				{
					SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array($currentTime, $user));

					return TRUE;
				}
				else
				{
					SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array($currentTime, $user));

					return FALSE;
				}
			}
			else
			{
				SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(1, $currentTime, $user));

				return FALSE;
			}
		}
	}
	
	
	
	/**
	 * To check if the temporary password has expired.
	 * @return boolean
	 */
	public function checkIfTempPassExpired()
	{
		$result = SQL("SELECT `TEMP_TIME` FROM PASSWORD WHERE `USERID` = ?", array($this->userID));
			
		$currentTime = time();
		
		if ( ($currentTime - $result[0]['TEMP_TIME'])  >= AdvancedPasswordManagement::$tempPassExpiryTime)
			return TRUE;
		else
			return FALSE;
	}
	
	
	
	/**
	 * Function to generate and validate a temp password.
	 * @param String $tempPass
	 * @return boolean
	 */
	public function tempPassword($tempPass = "")
	{
		//If a temp password has not been provided, then create a temp password.
		if ($tempPass == "")
		{
			$tempPass = hash("sha512", randstr(64));
			$time = time();

			SQL("UPDATE PASSWORD SET `TEMP_PASS` = ?, `USE_FLAG` = ?, `TEMP_TIME` = ? WHERE USERID = ?", array($tempPass, 0, $time, $this->userID));

			return TRUE;
		}
		else	//If a temp pass is provided, then check if it is not expired and it correct.
		{
			$result = SQL("SELECT `TEMP_PASS`, `USE_FLAG`, `TEMP_TIME` FROM PASSWORD WHERE `USERID` = ?", array($this->userID));
				
			if ( ($result[0]['USE_FLAG'] == 0) && (!$this->checkIfTempPassExpired()))
			{	
				if ( $result[0]['TEMP_PASS'] != $tempPass )
					return FALSE;

				SQL("UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?", array(randstr(10), 1, 0, $this->userID));

				return TRUE;
			}
			else
			{
				SQL("UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?", array(randstr(10), 1, 0, $this->userID));

				return FALSE;
			}
		}
	}
}

?>
