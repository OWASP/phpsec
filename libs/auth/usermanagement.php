<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once 'user.php';



class UserManagement
{
	
	
	/**
	 * To check if a userID exists in the system or not.
	 * @param String $userID
	 * @return boolean
	 */
	public static function userExists($userID)
	{
		$result = SQL("SELECT USERID FROM USER WHERE USERID = ?", array($userID));
		
		return (count($result) == 1);
	}
	
	
	
	/**
	 * To create a new user.
	 * @param String $userID
	 * @param String $password
	 * @param String $staticSalt
	 * @return \phpsec\User | boolean
	 */
	public static function createUser($userID, $password, $staticSalt = "")
	{
		if (! UserManagement::userExists( $userID ))	//If the userId is available, then create a new user.
			return User::newUserObject($userID, $password, $staticSalt);
		
		return FALSE;
	}
	
	
	
	/**
	 * To delete a user.
	 * @param String $userID
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
	 * Function to return the total number of devices the user is logged in from.
	 * @param String $userID
	 * @return int
	 */
	public static function devicesLoggedIn($userID)
	{
		//Select all session IDs from Session table for this user.
		$result = SQL("SELECT `SESSION_ID` FROM SESSION WHERE USERID = ?", array($userID));
		$count = 0;
		
		//Filter all "DEV" sessions. Count of all those sessions is the total number of device logged-in.
		foreach ($result as $session)
		{
			if (\substr($session['SESSION_ID'], 0, 3) == "DEV")
				$count = $count + 1;
		}

		return $count;
	}
	
	
	
	/**
	 * Function to check if a user is logged-in to the system. True will be returned if the user is logged-in from any device.
	 * @param String $userID
	 * @return boolean
	 */
	public static function isLoggedIn($userID)
	{
		$count = UserManagement::devicesLoggedIn($userID);
		
		return ($count > 0);
	}
	
	
	
	/**
	 * Function for user to log-in.
	 * @param String $userID
	 * @param String $password
	 * @return \phpsec\User
	 */
	public static function logIn($userID, $password)
	{
		return User::existingUserObject($userID, $password);	//If any user credential is wrong, exception will be thrown.
	}
	
	
	
	/**
	 * Function for user to log-in forcefully i.e without providing user-credentials.
	 * @param String $userID
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
	 * @throws SessionNotFoundException
	 */
	public static function logOut($userObj)
	{
		//check if session is supported.
		if ( (isset($userObj->session[0])) && ($userObj->session[0] !== FALSE) )
		{
			//The session array inside user class stores sessions related to users log-in information. Deleting those sessions would result in log-out.
			foreach ($userObj->session as $session)
			{
				$session->destroySession();
			}
			
			return TRUE;
		}
		else
			throw new SessionNotFoundException("<BR>ERROR: Session is not Found. Session Library is needed to use this function.<BR>");
	}
	
	
	
	/**
	 * Function for user to log-out from all the devices at once.
	 * @param String $userID
	 * @return boolean
	 * @throws SessionNotFoundException
	 */
	public static function logOutFromAllDevices($userID)
	{
		$userObj = UserManagement::forceLogIn($userID);		//get user object for this userID.
		
		//check if session is supported.
		if ( (isset($userObj->session[0])) && ($userObj->session[0] !== FALSE) )
		{
			$userObj->session[0]->destroyAllSessions();	//destroy all session and session data related to this user. This would result in  logOut from all devices.
			
			return TRUE;
		}
		else
			throw new SessionNotFoundException("<BR>ERROR: Session is not Found. Session Library is needed to use this function.<BR>");
	}
}

?>