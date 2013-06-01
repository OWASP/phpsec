<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Time.class.php');
require_once (__DIR__ . '/../core/Rand.class.php');


class BasicPasswordManagement
{
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
class UserExistsException extends UserException {}

class User
{
	private $_handler = null;
	
	private $_userID = null;
	private $_firstName = null;
	private $_lastName = null;
	private $_email = null;
	
	private $_rawPassword = "";
	private $_hashedPassword = "";
	private $_dynamicSalt = "";
	
	public function __construct($dbConn, $id, $pass, $email)
	{
		$this->_handler = $dbConn;
		
		if ($this->_handler == null)
		{
			throw new DBHandlerForUserNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			$this->_userID = $id;
			$this->_rawPassword = $pass;
			$this->_email = $email;
			
			try
			{
				$time = Time::time();
				
				$this->_dynamicSalt = hash("sha512", Rand::generateRandom(64));
				$this->_hashedPassword = BasicPasswordManagement::hashPassword($this->_rawPassword, $this->_dynamicSalt, BasicPasswordManagement::$hashAlgo);

				$query = "INSERT INTO USER (`USERID`, `HASH`, `DATE_CREATED`, `TOTAL_SESSIONS`, `EMAIL`, `ALGO`, `DYNAMIC_SALT`) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$args = array("{$this->_userID}", $this->_hashedPassword, $time, 0, $this->_email, BasicPasswordManagement::$hashAlgo, $this->_dynamicSalt);
				$count = $this->_handler -> SQL($query, $args);
				
				if ($count == 0)
					throw new UserExistsException("<BR>ERROR: This User already exists in the DB.<BR>");
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public function setOptionalFields($firstName = "", $lastName = "")
	{
		$this->_firstName = $firstName;
		$this->_lastName = $lastName;
		
		try
		{
			$query = "UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ? WHERE USERID = ?";
			$args = array($this->_firstName, $this->_lastName, "{$this->_userID}");
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
}

?>