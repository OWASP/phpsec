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
		$count = SQL("DELETE FROM USER WHERE USERID = ?", array($userID));
		return ($count == 1);
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
	 * To check user Credentails.
	 * @param String $userID
	 * @param String $password
	 * @return boolean
	 */
	public static function validateUserCredentials($userID, $password)
	{
		try
		{
			User::existingUserObject($userID, $password);	//If any user credential is wrong, exception will be thrown.
			return TRUE;	//no exception thrown. Hence all credentials are valid.
		}
		catch (\phpsec\UserException $e)
		{
			return FALSE;	//exceptions thrown. Hence username/password incorrect.
		}
	}
}

?>