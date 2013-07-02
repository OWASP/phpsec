<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once (__DIR__ . '/../core/Time.php');
require_once (__DIR__ . '/../core/Rand.php');


class BasicPasswordManagement
{
	
	/**
	 * To store the static salt for password salting.
	 * @var String
	 */
	protected static $staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	
	
	/**
	 * To store the current hash algorithm in use.
	 * @var String
	 */
	public static $hashAlgo = "sha512";
	
	
	/**
	 * To return the current value of static salt in use.
	 * @return String.
	 */
	public static function getStaticSalt()
	{
		return BasicPasswordManagement::$staticSalt;
	}
	
	
	/**
	 * To create hash of a string using dynamic and static salt.
	 * @param String $pass
	 * @param String $dynamicSalt
	 * @param String $algo
	 * @return String
	 */
	public static function hashPassword($pass, $dynamicSalt = "", $algo = "")
	{
		//If dynamic salt is not present, create one.
		if ($dynamicSalt == "")
			$dynamicSalt = hash("sha512",Rand::generateRandom(64));
		
		//If algo is not defined, use sha512 by default.
		if ($algo == "")
			$algo = "sha512";
		
		return hash($algo, strtolower($dynamicSalt . $pass . BasicPasswordManagement::$staticSalt));
	}
	
	
	/**
	 * To check if hash from the new password is equal to the old password's hash.
	 * @param String $newPassword
	 * @param String $oldHash
	 * @param String $oldSalt
	 * @param String $oldAlgo
	 * @return boolean
	 */
	public static function validatePassword($newPassword, $oldHash, $oldSalt, $oldAlgo)
	{
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $oldSalt, $oldAlgo);
		
		if ($newHash == $oldHash)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To calculate entropy of a string.
	 * @param String $string
	 * @return float
	 */
	public static function Entropy($string)
	{
		$h=0;
		$size = strlen($string);
		
		//Calculate the occurence of each character and compare that number with the overall length of the string and put it in the entropy formula.
		foreach (count_chars($string, 1) as $v)
		{
			$p = $v/$size;
			$h -= $p*log($p)/log(2);
		}
		
		return $h;
	}
	
	
	/**
	 * To check if the string has ordered characters i.e. strings such as "abcd".
	 * @param String $string
	 * @param int $length
	 * @return boolean
	 */
	public static function hasOrderedCharacters($string, $length)
	{
		$length=(int)$length;
		
		$i = 0;
		$j = strlen($string);
		
		//Group all the characters into length 1, and calculate their ASCII value. If they are continous, then they contain ordered characters.
		$str = implode('', array_map(function($m) use (&$i, &$j)
		{
			return chr((ord($m[0]) + $j--) % 256) . chr((ord($m[0]) + $i++) % 256);
		}, str_split($string, 1)));
		
		return preg_match('#(.)(.\1){' . ($length - 1) . '}#', $str)==true;
	}
	
	
	/**
	 * To check if the string has keyboard ordered characters i.e. strings such as "qwert".
	 * @param String $string
	 * @param int $length
	 * @return boolean
	 */
	public static function hasKeyboardOrderedCharacters($string, $length)
	{
		$length=(int)$length;
		
		$i = 0;
		$j = strlen($string);
		
		//group all the characters into length 1, and calculate their positions. If the positions match with the value $keyboardSet, then they contain keyboard ordered characters.
		$str = implode('', array_map(function($m) use (&$i, &$j)
		{
			$keyboardSet="1234567890qwertyuiopasdfghjklzxcvbnm";
			return ((strpos($keyboardSet,$m[0]) + $j--) ) . ((strpos($keyboardSet,$m[0]) + $i++) );
		}, str_split($string, 1)));
		
		return preg_match('#(..)(..\1){' . ($length - 1) . '}#', $str)==true;
	}
	
	
	/**
	 * To check if the string is of a phone-number pattern.
	 * @param String $string
	 * @return boolean
	 */
	public static function isPhoneNumber($string)	//there are many cases that phone numbers can be arranged. Hence not all possible combinations were taken into account.
	{
		//If the string contains only numbers and the length of the string is between 6 and 13, it is possibly a phone number.
		preg_match_all ("/^(\+)?\d{6,13}$/i", $string, $matches);
		
		if (count($matches[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To check if the string contains a phone-number pattern.
	 * @param String $string
	 * @return boolean
	 */
	public static function containsPhoneNumber($string)	//there are many cases that phone numbers can be arranged. Hence not all possible combinations were taken into account.
	{
		//If the string contains continous numbers of length beteen 6 and 13, then it is possible that the string contains a phone-number pattern. e.g. owasp+91917817
		preg_match_all ("/(\+)?\d{6,13}/i", $string, $matches);
		
		if (count($matches[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To check if the string is of a date-like pattern.
	 * @param String $string
	 * @return boolean
	 */
	public static function isDate($string)
	{
		//This checks dates of type Date-Month-Year (all digits)
		preg_match_all ("/^(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches1);
		//This checks dates of type Date-Month-Year (where month is represented by string)
		preg_match_all ("/^(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches2);
		
		//This checks dates of type Month-Date-Year (all digits)
		preg_match_all ("/^(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches3);
		//This checks dates of type Month-Date-Year (where month is represented by string)
		preg_match_all ("/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches4);
		
		//This checks dates of type Year-Month-Date (all digits)
		preg_match_all ("/^((19|20)?\d\d)[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])$/i", $string, $matches5);
		//This checks dates of type Year-Month-Date (where month is represented by string)
		preg_match_all ("/^((19|20)?\d\d)[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])$/i", $string, $matches6);
		
		//If any of the above conditions becomes true, then there is a date pattern.
		if (count($matches1[0])>=1 || count($matches2[0])>=1 || count($matches3[0])>=1 || count($matches4[0])>=1 || count($matches5[0])>=1 || count($matches6[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To check if the string contains a date-like pattern.
	 * @param String $string
	 * @return boolean
	 */
	public static function containsDate($string)
	{
		//This checks dates of type Date-Month-Year (all digits)
		preg_match_all ("/(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches1);
		//This checks dates of type Date-Month-Year (where month is represented by string)
		preg_match_all ("/(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches2);
		
		//This checks dates of type Month-Date-Year (all digits)
		preg_match_all ("/(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches3);
		//This checks dates of type Month-Date-Year (where month is represented by string)
		preg_match_all ("/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches4);
		
		//This checks dates of type Year-Month-Date (all digits)
		preg_match_all ("/((19|20)?\d\d)[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])/i", $string, $matches5);
		//This checks dates of type Year-Month-Date (where month is represented by string)
		preg_match_all ("/((19|20)?\d\d)[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])/i", $string, $matches6);
		
		//If any of the above conditions becomes true, then there is a date pattern.
		if (count($matches1[0])>=1 || count($matches2[0])>=1 || count($matches3[0])>=1 || count($matches4[0])>=1 || count($matches5[0])>=1 || count($matches6[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To check if the string contains double words such as crabcrab, stopstop, treetree, passpass, etc.
	 * @param String $string
	 * @return boolean
	 */
	public static function containDoubledWords($string)
	{
		//divide the string into two halves.
		$firstHalf = substr($string, 0, (strlen($string) / 2));
		$secondHalf = substr($string, (strlen($string) / 2), strlen($string));
		
		//check for the equality of the two words.
		if ($firstHalf == $secondHalf)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To check if the given string($hay) contains another string ($needle) in it.
	 * @param String $hay
	 * @param String $needle
	 * @return boolean
	 */
	public static function containsString($hay, $needle)	//used for checking for usernames, firstname, lastname etc.
	{
		preg_match_all("/(" . $needle . ")/i", $hay, $matches);
		
		if (count($matches[0]) >= 1)
			return TRUE;
		else
			return FALSE;
	}
	
	
	/**
	 * To calculate the strength of a given string. The value lies between 0 and 1 where 1 being the strongest.
	 * @param String $RawPassword
	 * @return float
	 */
	public static function strength($RawPassword)
	{
		$score=0;

		//initial score is the entropy of the password
		$entropy=self::Entropy($RawPassword);
		$score+=$entropy/4; //maximum entropy is 8

		//check for sequence of letters
		$ordered=self::hasOrderedCharacters($RawPassword, strlen($RawPassword)/2);
		$fullyOrdered=self::hasOrderedCharacters($RawPassword, strlen($RawPassword));
		$hasKeyboardOrder=self::hasKeyboardOrderedCharacters($RawPassword,strlen($RawPassword)/2);
		$keyboardOrdered=self::hasKeyboardOrderedCharacters($RawPassword,strlen($RawPassword));


		if ($fullyOrdered)
			$score*=.1;
		elseif ($ordered)
			$score*=.5;

		if ($keyboardOrdered)
			$score*=.15;
		elseif ($hasKeyboardOrder)
			$score*=.5;

		//check for date patterns
		if (self::isDate( $RawPassword))
			$score*=.2;
		elseif (self::containsDate( $RawPassword))
			$score*=.5;

		//check for phone numbers
		if (self::isPhoneNumber( $RawPassword))
			$score*=.5;
		elseif (self::containsPhoneNumber( $RawPassword))
			$score*=.9;
		
		if (self::containDoubledWords( $RawPassword))
			$score*=.3;

		//check for variety of character types
		preg_match_all ("/\d/i", $RawPassword, $matches);
		$numbers = count($matches[0])>=1;

		preg_match_all ("/[a-z]/", $RawPassword, $matches);
		$lowers = count($matches[0])>=1;

		preg_match_all ("/[A-Z]/", $RawPassword, $matches);
		$uppers = count($matches[0])>=1;

		preg_match_all ("/[^A-z0-9]/", $RawPassword, $matches);
		$others = count($matches[0])>=1;

		$setMultiplier=($others+$uppers+$lowers+$numbers)/4;

		$score=$score/2 + $score/2*$setMultiplier;


		return min(1,max(0,$score));

	}
	
	
	/**
	 * To generate a random string of specified strength.
	 * @param float $Security
	 * @return String
	 */
	public static function generate($Security=.5)
	{
		$MaxLen=20;
		
		if ($Security>.3)
			$UseNumbers=true;	//can use digits.
		else
			$UseNumbers=false;
		
		if ($Security>.5)
			$UseUpper=true;		//can use upper case letters.
		else
			$UseUpper=false;
		
		if ($Security>.9)
			$UseSymbols=true;	//can use symbols such as %, &, # etc.
		else
			$UseSymbols=false;
		
		
		$Length=max($Security*$MaxLen,4);

		$chars='abcdefghijklmnopqrstuvwxyz';
		
		if ($UseUpper)
			$chars.="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		
		if ($UseNumbers)
			$chars.="0123456789";
		
		if ($UseSymbols)
			$chars.="!@#$%^&*()_+-=?.,";

		$Pass="";
		//$char contains the string that has all the letters we can use in a password.
		//The loop pics a character from $char in random and adds that character to the final $pass variable.
		for ($i=0;$i<$Length;++$i)
			$Pass.=$chars[Rand::randLen(0, strlen($chars)-1)];
		
		return $Pass;
	}
}


class UserException extends \Exception {}

class DBHandlerForUserNotSetException extends UserException {}
class InvalidHashException extends UserException {}
class WrongPasswordException extends UserException {}
class UserExistsException extends UserException {}
class UserObjectNotReturnedException extends UserException {}

class ObjectAlreadyPresentInDB extends UserException {}

class SaltAlreadyPresentInDB extends ObjectAlreadyPresentInDB {}

class User extends BasicPasswordManagement
{
	protected $_handler = null;
	
	protected $_userID = null;
	
	protected $_hashedPassword = "";
	protected $_dynamicSalt = "";
	
	protected static $_passwordExpiryTime = 15552000;	//approx 6 months.
	
	public static function newUserObject($dbConn, $id, $pass, $staticSalt = "")
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
			
			if ($staticSalt != "")
				$obj->setStaticSalt ( $staticSalt );
			
			try
			{
				$time = Time::time();
				
				$obj->_dynamicSalt = hash("sha512", Rand::generateRandom(64));
				$obj->_hashedPassword = BasicPasswordManagement::hashPassword($pass, $obj->_dynamicSalt, BasicPasswordManagement::$hashAlgo);

				$query = "INSERT INTO USER (`USERID`, `ACCOUNT_CREATED`, `HASH`, `DATE_CREATED`, `TOTAL_SESSIONS`, `ALGO`, `DYNAMIC_SALT`, `STATIC_SALT`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
				$args = array("{$obj->_userID}", $time, $obj->_hashedPassword, $time, 0, BasicPasswordManagement::$hashAlgo, $obj->_dynamicSalt, BasicPasswordManagement::$staticSalt);
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
				$query = "SELECT `HASH`, `ALGO`, `DYNAMIC_SALT`, `STATIC_SALT` FROM USER WHERE `USERID` = ?";
				$args = array($id);
				$result = $obj->_handler -> SQL($query, $args);
				
				if (count($result) < 1)
					throw new UserObjectNotReturnedException("<BR>ERROR: User Object not returned.<BR>");

				BasicPasswordManagement::$staticSalt = $result[0]['STATIC_SALT'];
				
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
	
	private function setStaticSalt($newSalt)
	{
		BasicPasswordManagement::$staticSalt = $newSalt;
		
		try
		{
			$query = "INSERT INTO STATIC_SALT (`STATICSALT`) VALUES (?)";
			$args = array(BasicPasswordManagement::$staticSalt);
			$count = $this->_handler -> SQL($query, $args);
			
			if ($count == 0)
				throw new SaltAlreadyPresentInDB("This static-salt is already present in the DB. Please choose a different salt.");
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	public function setOptionalFields($email = "", $firstName = "", $lastName = "")
	{
		try
		{
			$query = "UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ?, EMAIL = ? WHERE USERID = ?";
			$args = array($firstName, $lastName, $email, "{$this->_userID}");
			$count = $this->_handler -> SQL($query, $args);
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
	
	public function getAccountCreationDate()
	{
		try
		{
			$query = "SELECT `ACCOUNT_CREATED` FROM USER WHERE USERID = ?";
			$args = array("{$this->_userID}");
			$result = $this->_handler -> SQL($query, $args);
			
			return $result[0]['ACCOUNT_CREATED'];
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
		
		$query = "UPDATE USER SET `HASH` = ?, `DATE_CREATED` = ?, `DYNAMIC_SALT` = ?, `ALGO` = ? WHERE `USERID` = ?";
		$args = array($newHash, Time::time(), $this->_dynamicSalt, BasicPasswordManagement::$hashAlgo, $this->_userID);
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
	
	public static function setPasswordExpiryTime($time)
	{
		if( ( gettype($time) != "integer" ) )
			throw new \Exception("<BR>ERROR: Integer is required. " . gettype($time) . " was found.<BR>");
		
		User::$_passwordExpiryTime = $time;
	}
	
	public static function getPasswordExpiryTime()
	{
		return User::$_passwordExpiryTime;
	}
	
	public function checkIfPasswordExpired()
	{
		try
		{
			$query = "SELECT `DATE_CREATED` FROM USER WHERE `USERID` = ?";
			$args = array($this->_userID);
			$result = $this->_handler->SQL($query, $args);
			
			$currentTime = Time::time();
		
			if ( ($currentTime - $result[0]['DATE_CREATED'])  > User::$_passwordExpiryTime)
				return TRUE;
			else
				return FALSE;
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
