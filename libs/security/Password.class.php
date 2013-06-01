<?php
namespace phpsec;

require_once __DIR__ . '/../core/Rand.class.php';
require_once __DIR__ . '/../core/Time.class.php';

class PasswordException extends \Exception {}

class InvalidHashException extends PasswordException {}
class DBHandlerForPasswordNotSetException extends PasswordException {}
class PasswordAlreadySetException extends PasswordException {}

class Password
{
	private $_handler = "";
	private $_username = "";
	private $_rawPassword = "";
	
	private static $_staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	private $_dynamicSalt = "";
	
	private $_hashedPassword = "";
	
	public static $hashAlgo = "";
	
	public function __construct($dbConn, $user, $pass, $dynamicSalt = "", $algo = "")
	{
		$this->_handler = $dbConn;
		$this->_username = $user;
		$this->_rawPassword = $pass;
		$this->_dynamicSalt = $dynamicSalt;
		Password::$hashAlgo = $algo;
		
		$this->_hashedPassword = $this->hashPassword($this->_username, $this->_rawPassword, $this->_dynamicSalt, Password::$hashAlgo);
	}
	
	public function getUsername()
	{
		return $this->_username;
	}
	
	public function getHashedPassword()
	{
		if ($this->_hashedPassword == "")
			throw new InvalidHashException("<BR>WARNING: This hash seems invalid.<BR>");
		else
			return $this->_hashedPassword;
	}
	
	public static function getStaticSalt()
	{
		return Salt::$_staticSalt;
	}
	
	public function getDynamiSalt()
	{
		return $this->_dynamicSalt;
	}
	
	public function hashPassword($user, $pass, $dynamicSalt = "", $algo = "")
	{
		if ($dynamicSalt == "")
			$dynamicSalt = hash("sha512",Rand::generateRandom(32));
		
		if ($algo == "")
			$algo = "sha512";
		
		return hash($algo, strtolower($user . $dynamicSalt . $pass . Password::$_staticSalt));
	}
	
	public function commitPasswordToDB()
	{
		if ($this->_handler == null)
		{
			throw new DBHandlerForPasswordNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		
		try
		{
			$hashPassword = $this->getHashedPassword();
			$time = Time::time();

			$query = "INSERT INTO PASSWORD (`HASH`, `DYNAMIC_SALT`, `ALGO`, `DATE_CREATED`, `USERID`) VALUES (?, ?, ?, ?, ?)";
			$args = array("{$hashPassword}", "{$this->getDynamiSalt()}", Password::$hashAlgo, $time, "{$this->_username}");
			$count = $this->_handler -> SQL($query, $args);
			
			if($count == 0)
			{
				throw new PasswordAlreadySetException("<BR>ERROR: Password for this user is already set. Cannot set duplicate password for 1 user. To change the password use function \"resetPassword\"<BR>");
			}
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	public function validatePassword($password)
	{
		try
		{
			$query = "SELECT `HASH`, `DYNAMIC_SALT`, `ALGO` FROM PASSWORD WHERE `USERID` = ?";
			$args = array("{$this->_username}");
			$result = $this->_handler -> SQL($query, $args);
			
			$hash = $result[0]['HASH'];
			$dynamicSalt = $result[0]['DYNAMIC_SALT'];
			$algo = $result[0]['ALGO'];
			
			$newHash = $this->hashPassword($this->_username, $password, $dynamicSalt, $algo);
			
			if ($hash == $newHash)
				return TRUE;
			else
				return FALSE;
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
}

?>