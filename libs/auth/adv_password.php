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
	 * It denotes the # of maximum attempts for login using the password. If this limit exceeds and this happens within a very short amount of time (which is defined by $bruteForceLockAttemptTotalTime), then it is considered as a brute force attack.
	 * @var int
	 */
	public static $bruteForceLockAttempts = 5;	//This tells how many attemps must be considered before brute-force lock within a time span defined by $bruteForceLockAttemptTotalTime.
	
	
	/**
	 * It denotes the amount of time in seconds between which no two wrong passwords must be entered. If this happens, then it is considered that a bot is trying to hack the account using brute-force means.
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
	 * 2) More than {$bruteForceLockAttempts} this many login attempts are made within {$bruteForceLockAttemptTotalTime} this time span. i.e. for e.g. if more than 5 failed login attempts are made within a span of 25 seconds, then it will be an odd behaviour that a human can't show. That means its a brute force attack.
	 */
	
	
	/**
	 * Constructor for the AdvancedPasswordManagement Class.
	 * @param String $user		//The ID of the user.
	 * @param String $pass		//The password of the user.
	 * @param boolean $bruteLock	//If brute force detection is enabled.
	 * @throws BruteForceAttackDetectedException
	 * @throws \phpsec\WrongPasswordException
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
				$bruteFound = $this->isBruteForce($user);
				
				if ($bruteFound)
					throw new BruteForceAttackDetectedException($e->getMessage ( ) . "\nWARNING: Brute Force Attack Detected. We Recommend you use captcha.");
			}
			else
				throw $e;
		}
		
		if (! AdvancedPasswordManagement::checkIfUserExists($user))
			SQL("INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, `USERID`) VALUES (?, ?, ?, ?)", array(randstr(10), 1, 0, $user));
	}
	
	
	
	/**
	 * Function to check if the user exists in PASSWORD table or not.
	 * @param string $userID
	 * @return boolean
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
	 * @param string $user
	 * @return boolean
	 */
	protected function isBruteForce($user)
	{
		$currentTime = time();
			
		$result = SQL("SELECT `TOTAL_LOGIN_ATTEMPTS`, `LAST_LOGIN_ATTEMPT`, `FIRST_LOGIN_ATTEMPT` FROM PASSWORD WHERE USERID = ?", array($user));
		
		//if first_login_attempt OR last_login_attempt are not set, then set them and return false.
		if ( ($result[0]['FIRST_LOGIN_ATTEMPT'] == 0) || ($result[0]['LAST_LOGIN_ATTEMPT'] == 0) )
		{
			SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array($currentTime, $currentTime, $user));
			return FALSE;
		}
		
		//if two failed login attempts are made within $bruteForceLockTimePeriod time period, then reset the counters and return true to declare this a brute force attack.
		if ( ($currentTime - $result[0]['LAST_LOGIN_ATTEMPT']) <= AdvancedPasswordManagement::$bruteForceLockTimePeriod )
		{
			SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(0, 0, 0, $user));
			return TRUE;
		}
		
		//check if two subsequent requests are made within $bruteForceLockAttemptTotalTime time-period.
		if ( ($currentTime - $result[0]['FIRST_LOGIN_ATTEMPT']) <= AdvancedPasswordManagement::$bruteForceLockAttemptTotalTime )
		{
			// To check how many total failed attempts have happened. If more than $bruteForceLockAttempts attempts have happened, then that is an attack. Hence we reset the counters and return TRUE.
			if ($result[0]['TOTAL_LOGIN_ATTEMPTS'] >= AdvancedPasswordManagement::$bruteForceLockAttempts)
			{
				SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(0, 0, 0, $user));
				return TRUE;
			}
			else	//since the total login attempts have not crossed $bruteForceLockAttempts, this is not a brute force attack. Hence we just update our counters.
			{
				SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = `TOTAL_LOGIN_ATTEMPTS` + 1, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array($currentTime, $user));
				return FALSE;
			}
		}
		else	//since difference between two failed login requests are out of $bruteForceLockAttemptTotalTime time period, we can safely reset all the counters and TELL THAT THIS IS NOT A BRUTE FORCE ATTACK.
		{
			SQL("UPDATE PASSWORD SET `TOTAL_LOGIN_ATTEMPTS` = ?, `FIRST_LOGIN_ATTEMPT` = ?, `LAST_LOGIN_ATTEMPT` = ? WHERE USERID = ?", array(0, 0, 0, $user));
			return FALSE;
		}
	}
	
	
	
	/**
	 * To check if the temporary password has expired. Returns false if time not expired, else returns true.
	 * @return boolean
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
	 * @param String $tempPass
	 * @return boolean
	 */
	public static function tempPassword($userID, $tempPass = "")
	{
		//If a temp password has not been provided, then create a temp password.
		if ($tempPass == "")
		{
			$tempPass = hash(BasicPasswordManagement::$hashAlgo, randstr(64));
			$time = time();
			
			if (! AdvancedPasswordManagement::checkIfUserExists($userID))
				SQL("INSERT INTO PASSWORD (`TEMP_PASS`, `USE_FLAG`, `TEMP_TIME`, USERID) VALUES (?, ?, ?, ?)", array($tempPass, 0, $time, $userID));
			else
				SQL("UPDATE PASSWORD SET `TEMP_PASS` = ?, `USE_FLAG` = ?, `TEMP_TIME` = ? WHERE USERID = ?", array($tempPass, 0, $time, $userID));
			
			return $tempPass;
		}
		else	//If a temp pass is provided, then check if it is not expired and it correct.
		{
			$result = SQL("SELECT `TEMP_PASS`, `USE_FLAG` FROM PASSWORD WHERE `USERID` = ?", array($userID));
			
			if (count($result) == 1)
			{
				if ( ($result[0]['USE_FLAG'] == 0) && (! $a = AdvancedPasswordManagement::checkIfTempPassExpired($userID)) )
				{	
					if ( $result[0]['TEMP_PASS'] === $tempPass )
					{
						SQL("UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?", array(randstr(10), 1, 0, $userID));
						return TRUE;
					}
				}
				else
				{
					SQL("UPDATE PASSWORD SET TEMP_PASS = ?, USE_FLAG = ?, TEMP_TIME = ? WHERE USERID = ?", array(randstr(10), 1, 0, $userID));
					return FALSE;
				}
			}
			
			return FALSE;
		}
	}
}

?>
