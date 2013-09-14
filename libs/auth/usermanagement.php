<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once 'user.php';
require_once (__DIR__ . '/../core/random.php');
require_once (__DIR__ . '/../core/time.php');



class UserManagement
{
	
	
	/**
	 * To check if a userID exists in the system or not.
	 * @param string $userID
	 * @return boolean
	 */
	public static function userExists($userID)
	{
		$result = SQL("SELECT USERID FROM USER WHERE USERID = ?", array($userID));
		
		return (count($result) == 1);
	}
	
	
	
	/**
	 * To create a new user.
	 * @param string $userID
	 * @param string $password
	 * @return \phpsec\User | boolean
	 */
	public static function createUser($userID, $password, $email)
	{
		if (! UserManagement::userExists( $userID ))	//If the userId is available, then create a new user.
			return User::newUserObject($userID, $password, $email);
		
		return FALSE;
	}
	
	
	
	/**
	 * To delete a user.
	 * @param string $userID
	 * @return boolean
	 */
	public static function deleteUser($userID)
	{
		$userObj = UserManagement::forceLogIn($userID);
		$deleted = $userObj->deleteUser();
		
		return ($deleted == TRUE);
	}
	
	
	
	/**
	 * To return the total number of users in the system.
	 * @return int
	 */
	public static function userCount()
	{
		$result = SQL("SELECT USERID FROM USER WHERE 1", array());
		return count($result);
	}
	
	
	
	/**
	 * Function for user to log-in.
	 * @param string $userID
	 * @param string $password
	 * @return \phpsec\User
	 */
	public static function logIn($userID, $password)
	{
		return User::existingUserObject($userID, $password);	//If any user credential is wrong, exception will be thrown.
	}
	
	
	
	/**
	 * Function for user to log-in forcefully i.e without providing user-credentials.
	 * @param string $userID
	 * @return \phpsec\User
	 */
	public static function forceLogIn($userID)
	{
		return User::forceLogin($userID);
	}
	
	
	
	/**
	 * Function for user to Log-out.
	 * @param \phpsec\User $userObj
	 * @return boolean
	 */
	public static function logOut($userObj)
	{
		if ($userObj->checkRememberMe() === TRUE)
		{
			\setcookie("AUTHID", "");
		}
		
		if (  file_exists(__DIR__ . "/../session/session.php") )
		{
			require_once (__DIR__ . "/../session/session.php");
			$tempSession = new Session();
			$tempSession->existingSession();
			$tempSession->destroySession();
		}
	}
	
	
	
	/**
	 * Function for user to log-out from all the devices at once.
	 * @param string $userID
	 * @return boolean
	 */
	public static function logOutFromAllDevices($userID)
	{
		$userObj = UserManagement::forceLogIn($userID);		//get user object for this userID.
		
		SQL("DELETE FROM `AUTH_TOKEN` WHERE USERID = ?", array($userObj->getUserID()));
		
		if (  file_exists(__DIR__ . "/../session/session.php") )
		{
			require_once (__DIR__ . "/../session/session.php");
			$tempSession = new Session();
			$tempSession->existingSession();
			$tempSession->destroyAllSessions();
		}
	}
}

?>