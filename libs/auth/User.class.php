<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Time.class.php');
require_once (__DIR__ . '/../core/Rand.class.php');


class BasicPasswordManagement
{
	/**
	 * Changing this salt in application, would invalidate all previous passwords, because their static salt would change.
	 * @var type 
	 */
	protected static $_staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	public static $hashAlgo = "sha512";
	
	
	public static function getStaticSalt()
	{
		return BasicPasswordManagement::$_staticSalt;
	}
	
	public static function hashPassword($pass, $dynamicSalt = "", $algo = "")
	{
		if ($dynamicSalt == "")
			$dynamicSalt = hash("sha512",Rand::generateRandom(64));
		
		if ($algo == "")
			$algo = "sha512";
		
		return hash($algo, strtolower($dynamicSalt . $pass . BasicPasswordManagement::$_staticSalt));
	}
	
	public static function validatePassword($newPassword, $oldHash, $oldSalt, $oldAlgo)
	{
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $oldSalt, $oldAlgo);
		
		if ($newHash == $oldHash)
			return TRUE;
		else
			return FALSE;
	}
}


class UserException extends \Exception {}

class DBHandlerForUserNotSetException extends UserException {}
class InvalidHashException extends UserException {}
class WrongPasswordException extends UserException {}
class UserExistsException extends UserException {}
class UserObjectNotReturnedException extends UserException {}

class User
{
	private $_handler = null;
	
	private $_userID = null;
	
	private $_hashedPassword = "";
	private $_dynamicSalt = "";
	
	public static function newUserObject($dbConn, $id, $pass, $email)
	{
		$obj = new User();
		
		$obj->_handler = $dbConn;
		
		if ($obj->_handler == null)
		{
			throw new DBHandlerForUserNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			$obj->_userID = $id;
			
			try
			{
				$time = Time::time();
				
				$obj->_dynamicSalt = hash("sha512", Rand::generateRandom(64));
				$obj->_hashedPassword = BasicPasswordManagement::hashPassword($pass, $obj->_dynamicSalt, BasicPasswordManagement::$hashAlgo);

				$query = "INSERT INTO USER (`USERID`, `HASH`, `DATE_CREATED`, `TOTAL_SESSIONS`, `EMAIL`, `ALGO`, `DYNAMIC_SALT`) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$args = array("{$obj->_userID}", $obj->_hashedPassword, $time, 0, $email, BasicPasswordManagement::$hashAlgo, $obj->_dynamicSalt);
				$count = $obj->_handler -> SQL($query, $args);
				
				if ($count == 0)
					throw new UserExistsException("<BR>ERROR: This User already exists in the DB.<BR>");
				
				return $obj;
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public static function existingUserObject($dbConn, $id, $pass)
	{
		$obj = new User();
		
		$obj->_handler = $dbConn;
		
		if ($obj->_handler == null)
		{
			throw new DBHandlerForUserNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			try
			{
				$query = "SELECT `HASH`, `ALGO`, `DYNAMIC_SALT` FROM USER WHERE `USERID` = ?";
				$args = array($id);
				$result = $obj->_handler -> SQL($query, $args);
				
				if (count($result) < 1)
					throw new UserObjectNotReturnedException("<BR>ERROR: User Object not returned.<BR>");

				if (!BasicPasswordManagement::validatePassword( $pass, $result[0]['HASH'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']))
					throw new WrongPasswordException("<BR>ERROR: Wrong Password. User Object not returned.<BR>");

				$obj->_userID = $id;
				$obj->_dynamicSalt = $result[0]['DYNAMIC_SALT'];
				$obj->_hashedPassword = $result[0]['HASH'];
				BasicPasswordManagement::$hashAlgo = $result[0]['ALGO'];

				return $obj;
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public function setOptionalFields($firstName = "", $lastName = "")
	{
		try
		{
			$query = "UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ? WHERE USERID = ?";
			$args = array($firstName, $lastName, "{$this->_userID}");
			$count = $this->_handler -> SQL($query, $args);
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	public function getUserID()
	{
		return $this->_userID;
	}
	
	public function getHashedPassword()
	{
		if ($this->_hashedPassword == "")
			throw new InvalidHashException("<BR>WARNING: This hash seems invalid.<BR>");
		else
			return $this->_hashedPassword;
	}
	
	public function getDynamiSalt()
	{
		return $this->_dynamicSalt;
	}
	
	public function resetPassword($oldPassword, $newPassword)
	{
		if (! BasicPasswordManagement::validatePassword( $oldPassword, $this->_hashedPassword, $this->_dynamicSalt, BasicPasswordManagement::$hashAlgo))
			throw new WrongPasswordException("<BR>ERROR: Wrong Password provided!!<BR>");
		
		$this->_dynamicSalt = hash("sha512", Rand::generateRandom(64));
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $this->getDynamiSalt(), BasicPasswordManagement::$hashAlgo);
		
		$query = "UPDATE PASSWORD SET `HASH` = ?, `DYNAMIC_SALT` = ?, `ALGO` = ? WHERE `USERID` = ?";
		$args = array($newHash, $this->_dynamicSalt, BasicPasswordManagement::$hashAlgo, $this->_userID);
		$count = $this->_handler -> SQL($query, $args);
		
		$this->_hashedPassword = $newHash;

		return TRUE;
	}
	
	public function deleteUser()
	{
		try
		{
			$query = "DELETE FROM USER WHERE USERID = ?";
			$args = array("{$this->_userID}");
			$count = $this->_handler -> SQL($query, $args);
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	public function __destruct()
	{
		$this->_handler = null;
		$this->_userID = null;
		$this->_dynamicSalt = null;
		$this->_hashedPassword = null;
	}
}

?>