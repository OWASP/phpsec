<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once (__DIR__ . '/../core/time.php');
require_once (__DIR__ . '/../core/random.php');


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
	protected static function hashPassword($pass, $dynamicSalt = "", $algo = "")
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
	protected static function validatePassword($newPassword, $oldHash, $oldSalt, $oldAlgo)
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


/**
 * Parent Exception Class.
 */
class UserException extends \Exception {}

/**
 * Child Exception Classes
 */
class InvalidHashException extends UserException {}			//The hash returned is not valid. i.e. it is empty.
class WrongPasswordException extends UserException {}			//The password provided for the existing user is not correct.
class UserExistsException extends UserException {}			//No records were found with this userID in the database.
class UserObjectNotReturnedException extends UserException {}		//Cannot return the userObject.
class SaltAlreadyPresentInDB extends UserException {}			//The provided salt is already present in the DB.


class User extends BasicPasswordManagement
{
	
	/**
	 * To store the ID of the user.
	 * @var String
	 */
	protected $userID = null;
	
	
	/**
	 * To store the hash of the user password.
	 * @var String
	 */
	private $hashedPassword = "";
	
	
	/**
	 * To store the dynamic salt used in creating the hash of the password.
	 * @var String
	 */
	private $dynamicSalt = "";
	
	
	/**
	 * To denote the time after which a password must expire i.e. the password needs to be replaced.
	 * @var int
	 */
	public static $passwordExpiryTime = 15552000;	//approx 6 months.
	
	
	
	/**
	 * To create an object for a new user.
	 * @param DatabaseObject $dbConn
	 * @param String $id
	 * @param String $pass
	 * @param String $staticSalt
	 * @return \phpsec\User
	 * @throws DBHandlerForUserNotSetException
	 * @throws UserExistsException
	 */
	public static function newUserObject($id, $pass, $staticSalt = "")
	{
		$obj = new User();
		
		$obj->userID = $id;
			
		//If static salt is provided, then use the new static salt, not the default one set.
		if ($staticSalt != "")
			BasicPasswordManagement::$staticSalt = $staticSalt;

		$time = Time::time();

		//calculate the hash of the password.
		$obj->dynamicSalt = hash("sha512", Rand::generateRandom(64));
		$obj->hashedPassword = BasicPasswordManagement::hashPassword($pass, $obj->dynamicSalt, BasicPasswordManagement::$hashAlgo);

		$count = SQL("INSERT INTO USER (`USERID`, `ACCOUNT_CREATED`, `HASH`, `DATE_CREATED`, `TOTAL_SESSIONS`, `ALGO`, `DYNAMIC_SALT`, `STATIC_SALT`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array("{$obj->userID}", $time, $obj->hashedPassword, $time, 0, BasicPasswordManagement::$hashAlgo, $obj->dynamicSalt, BasicPasswordManagement::$staticSalt));

		//If the user is already present in the database, then a duplicate won't be created and no rows will be affected. Hence 0 will be returned.
		if ($count == 0)
			throw new UserExistsException("<BR>ERROR: This User already exists in the DB.<BR>");

		return $obj;
	}
	
	
	/**
	 * To get the object of an existing user.
	 * @param DatabaseObject $dbConn
	 * @param String $id
	 * @param String $pass
	 * @return \phpsec\User
	 * @throws DBHandlerForUserNotSetException
	 * @throws UserObjectNotReturnedException
	 * @throws WrongPasswordException
	 */
	public static function existingUserObject($id, $pass)
	{
		$obj = new User();
		
		$result = SQL("SELECT `HASH`, `ALGO`, `DYNAMIC_SALT`, `STATIC_SALT` FROM USER WHERE `USERID` = ?", array($id));

		//If no record is returned for this user, then this user does not exist in the system.
		if (count($result) < 1)
			throw new UserObjectNotReturnedException("<BR>ERROR: User Object not returned.<BR>");

		//extract static salt used while password generation
		BasicPasswordManagement::$staticSalt = $result[0]['STATIC_SALT'];

		//validate the given password with that stored in the DB.
		if (!BasicPasswordManagement::validatePassword( $pass, $result[0]['HASH'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']))
			throw new WrongPasswordException("<BR>ERROR: Wrong Password. User Object not returned.<BR>");

		//If all goes right, then set the local variables and return the user object.
		$obj->userID = $id;
		$obj->dynamicSalt = $result[0]['DYNAMIC_SALT'];
		$obj->hashedPassword = $result[0]['HASH'];
		BasicPasswordManagement::$hashAlgo = $result[0]['ALGO'];

		return $obj;
	}
	
	
	/**
	 * To get the date when the user account was created. The value returned is the UNIX timestamp.
	 * @return int
	 */
	public function getAccountCreationDate()
	{
		$result = SQL("SELECT `ACCOUNT_CREATED` FROM USER WHERE USERID = ?", array("{$this->userID}"));

		return $result[0]['ACCOUNT_CREATED'];
	}
	
	
	/**
	 * To get the userID of the current User.
	 * @return String
	 */
	public function getUserID()
	{
		return $this->userID;
	}
	
	
	/**
	 * To verify if a given string is the correct password that is stored in the DB for the current user.
	 * @param String $password
	 * @return boolean
	 */
	public function verifyPassword($password)
	{
		return BasicPasswordManagement::validatePassword($password, $this->hashedPassword, $this->dynamicSalt, BasicPasswordManagement::$hashAlgo);
	}
	
	
	/**
	 * Function to facilitate the password reset for the current user.
	 * @param String $oldPassword
	 * @param String $newPassword
	 * @return boolean
	 * @throws WrongPasswordException
	 */
	public function resetPassword($oldPassword, $newPassword)
	{
		//If given password ($oldPassword) is not matched with the one stored in the DB.
		if (! BasicPasswordManagement::validatePassword( $oldPassword, $this->hashedPassword, $this->dynamicSalt, BasicPasswordManagement::$hashAlgo))
			throw new WrongPasswordException("<BR>ERROR: Wrong Password provided!!<BR>");
		
		//create a new dynamic salt.
		$this->dynamicSalt = hash("sha512", Rand::generateRandom(64));
		//create the hash of the new password.
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $this->dynamicSalt, BasicPasswordManagement::$hashAlgo);
		
		//update the old password with the new password.
		SQL("UPDATE USER SET `HASH` = ?, `DATE_CREATED` = ?, `DYNAMIC_SALT` = ?, `ALGO` = ? WHERE `USERID` = ?", array($newHash, Time::time(), $this->dynamicSalt, BasicPasswordManagement::$hashAlgo, $this->userID));
		
		$this->hashedPassword = $newHash;

		return TRUE;
	}
	
	
	/**
	 * To delete the current user.
	 */
	public function deleteUser()
	{
		SQL("DELETE FROM USER WHERE USERID = ?", array("{$this->userID}"));
	}
	
	
	/**
	 * To check if the password has aged. i.e. if the time has passed after which the password must be changed.
	 * @return boolean
	 */
	public function isPasswordExpired()
	{
		$result = SQL("SELECT `DATE_CREATED` FROM USER WHERE `USERID` = ?", array($this->userID));
			
		$currentTime = Time::time();

		if ( ($currentTime - $result[0]['DATE_CREATED'])  > User::$passwordExpiryTime)
			return TRUE;
		else
			return FALSE;
	}
}

?>
