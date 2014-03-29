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
class BruteForceAttackDetectedException extends WrongPasswordException {}	//Thrown when brute-force attack is detected



class AdvancedPasswordManagement
{



	/**
	 * ID of current user.
	 * @var string
	 */
	protected $userID = null;



	/**
	 * Time after which the temporary password generated for the current user must expire.
	 * @var int
	 */
	public static $tempPassExpiryTime = 900;	//15 min



	/**
	 * It denotes the # of maximum attempts for login using the password. If this limit exceeds and this happens within a very short amount of time (which is defined by $bruteForceLockAttemptTotalTime), then it is considered as a brute force attack.
	 * @var int
	 */
	public static $bruteForceLockAttempts = 5;



	/**
	 * It denotes the amount of time in seconds between which no two wrong passwords must be entered. If this happens, then it is considered that a bot is trying to hack the account using brute-force.
	 * @var int
	 */
	public static $bruteForceLockTimePeriod = 1;  //1 SEC  - This defines the time-period after which next login attempt must be carried out. E.g if the time is 1 sec, then time-period between two login attempts must minimum be 1 sec. Assuming that user will take atleast 1 sec time to type between two passwords.



	/**
	 * It denotes the amount of time in seconds within which total number of attempts ($bruteForceLockAttempts) must not exceed its maximum value. If this happens , then it is considered as a brute force attack.
	 * @var int
	 */
	public static $bruteForceLockAttemptTotalTime =25; //This tells that if ($bruteForceLockAttempts) login attempts are made within ($bruteForceLockAttemptTotalTime) time then it will be a brute force.



	/**
	 * In a summary, a brute force is when:
	 * 1) Two attempts are made within time specified by $bruteForceLockTimePeriod i.e. for e.g. two failed login attempts must not be made within 1 second. If this behaviour is seen, then it must be a bot because humans cant make login attempts this fast.
	 * 2) If more than {$bruteForceLockAttempts} login attempts are made within {$bruteForceLockAttemptTotalTime} time span. i.e. for e.g. if more than 5 failed login attempts are made within a span of 25 seconds, then it will be an odd behaviour that a human can't show. That means its a brute force attack.
	 */



	/**
	 * Constructor for the AdvancedPasswordManagement Class.
	 * @param String $userID		//The ID of the user.
	 * @param String $pass		//The password of the user.
	 * @param boolean $bruteLock	//True enables brute force detection. False disables this functionality
	 * @throws BruteForceAttackDetectedException	Will be thrown if brute-force attack is detected
	 * @throws WrongPasswordException	Will be thrown if the given password does not matches the old password stored in the DB
	 */
	public function __construct($userID, $pass, $bruteLock = false)
	{
		try
		{
			$this->userID = $userID;
			User::existingUserObject($userID, $pass);	//try to get the object of the user
		}
		catch(\phpsec\WrongPasswordException $e)	//will be thrown if wrong password is entered
		{
			if ($bruteLock == true)	//If brute-force detection is enabled, then check for brute-force
			{
				if ($this->isBruteForce($userID))	//If brute-force detected, throw the exception
					throw new BruteForceAttackDetectedException($e->getMessage ( ) . "\nWARNING: Brute Force Attack Detected. We Recommend you use captcha.");
			}
			else	//If brute-force is disabled, then just throw the exception
				throw $e;
		}

		if (! AdvancedPasswordManagement::checkIfUserExists($userID))	//If this user's record is NOT present in the PASSWORD table, then insert the new record for this user
			SQL("INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `USERID`) VALUES (?, ?, ?, ?)", array(randstr(10), 1, 0, $userID));
	}



	/**
	 * Function to check if the user exists in PASSWORD table or not.
	 * @param string $userID	The userID of the user
	 * @return boolean		Returns true if the record is present. False otherwise
	 */
	protected static function checkIfUserExists($userID)
	{
		$result = SQL("SELECT USERID FROM PASSWORD WHERE USERID = ?", array($userID));
		if(count($result) == 0)
			return FALSE;
		else
			return TRUE;
	}



	/**
	 * Function to detect brute-force attacks.
	 * @param string $userID	The userID of the user
	 * @return boolean		Returns True if brute-force is detected. False otherwise
	 */
	protected function isBruteForce($userID)
	{
		$currentTime = time();

		$result = SQL("SELECT `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT`, `FIRST_LOGIN_ATTEMPT` FROM PASSWORD WHERE USERID = ?", array($userID));

		//if first_login_attempt OR last_login_attempt are not set, then set them and return false.
		if ( ($result[0]['FIRST_LOGIN_ATTEMPT'] == 0) || ($result[0]['LAST_LOGIN_ATTEMPT'] == 0) )
		{
			SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array($currentTime, $currentTime, $userID));
			return FALSE;
		}

		//if two failed login attempts are made within $bruteForceLockTimePeriod time period, then reset the counters and return true to declare this a brute force attack.
		if ( ($currentTime - $result[0]['LAST_LOGIN_ATTEMPT']) <= AdvancedPasswordManagement::$bruteForceLockTimePeriod )
		{
			SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(0, 0, 0, $userID));
			return TRUE;
		}

		//check if two subsequent requests are made within $bruteForceLockAttemptTotalTime time-period.
		if ( ($currentTime - $result[0]['FIRST_LOGIN_ATTEMPT']) <= AdvancedPasswordManagement::$bruteForceLockAttemptTotalTime )
		{
			// To check how many total failed attempts have happened. If more than $bruteForceLockAttempts attempts have happened, then that is an attack. Hence we reset the counters and return TRUE.
			if ($result[0]['TOTAL_LOGIN_ATTEMPTS'] >= AdvancedPasswordManagement::$bruteForceLockAttempts)
			{
				SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(0, 0, 0, $userID));
				return TRUE;
			}
			else	//since the total login attempts have not crossed $bruteForceLockAttempts, this is not a brute force attack. Hence we just update our counters.
			{
				SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array($currentTime, $userID));
				return FALSE;
			}
		}
		else	//since difference between two failed login requests are out of $bruteForceLockAttemptTotalTime time period, we can safely reset all the counters and TELL THAT THIS IS NOT A BRUTE FORCE ATTACK.
		{
			SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(0, 0, 0, $userID));
			return FALSE;
		}
	}



	/**
	 * To check if the temporary password has expired.
	 * @return boolean	Returns false if time not expired, else returns true.
	 */
	public static function checkIfTempPassExpired($userID)
	{
		$result = SQL("SELECT `TEMP_TIME` FROM PASSWORD WHERE `USERID` = ?", array($userID));

		if (count($result) == 1)
		{
			if ( (time() - $result[0]['TEMP_TIME'])  < AdvancedPasswordManagement::$tempPassExpiryTime)
				return FALSE;
		}

		return TRUE;
	}



	/**
	 * Function to generate and validate a temporary password. To create a new temporary password, call this function without the second argument and the value returned will be the temporary password that will be sent to the user. To validate a temporary password, pass the temporary password to this function and will will return TRUE for valid passwords and FALSE for invalid/non-existent one's.
	 * @param string $userID	The userID of the user
	 * @param string $tempPass	The temporary password that needs to be checked if valid or not
	 * @return boolean | string	Returns True if temporary password provided is valid. False otherwise. Can also return temporary password in case where the temporary password needs to be set
	 */
	public static function tempPassword($userID, $tempPass = "")
	{
		//If a temp password has not been provided, then create a temp password.
		if ($tempPass == "")
		{
			$tempPass = hash(BasicPasswordManagement::$hashAlgo, randstr(128));
			$time = time();

			//If record is not present in the DB
			if (! AdvancedPasswordManagement::checkIfUserExists($userID))
				SQL("INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, USERID) VALUES (?, ?, ?, ?)", array($tempPass, 0, $time, $userID));
			else	//If record is present in the DB
				SQL("UPDATE PASSWORD SET `TEMP_PASS` = ?, `USE_FLAG` = ?, `TEMP_TIME` = ? WHERE USERID = ?", array($tempPass, 0, $time, $userID));

			return $tempPass;
		}
		else	//If a temp pass is provided, then check if it is not expired and it correct.
		{
			$result = SQL("SELECT `TEMP_PASS`, `USE_FLAG` FROM PASSWORD WHERE `USERID` = ?", array($userID));

			if (count($result) == 1)
			{
				//temporary password has not expired
				if ( ($result[0]['USE_FLAG'] == 0) && (! $a = AdvancedPasswordManagement::checkIfTempPassExpired($userID)) )
				{
					if ( $result[0]['TEMP_PASS'] === $tempPass )	//the provided password and the one stored in DB, matches. Hence return True
					{
						SQL("UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?", array(randstr(10), 1, 0, $userID));
						return TRUE;
					}
				}
				else	//temporary password has expired
				{
					SQL("UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?", array(randstr(10), 1, 0, $userID));
					return FALSE;
				}
			}
			//record not found
			return FALSE;
		}
	}
}

?>
