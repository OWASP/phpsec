<?php
namespace phpsec;



/**
 * Required Files
 */
require_once (__DIR__ . '/../core/random.php');
require_once (__DIR__ . '/../core/time.php');



class BasicPasswordManagement
{



	/**
	 * Current hash algorithm in use.
	 * @var string
	 */
	public static $hashAlgo = "sha512";



	/**
	 * Minimum password strength that all passwords must have.
	 * @var float
	 */
	public static $passwordStrength = 0.5;



	/**
	 * Current static salt in use.
	 * @return string	The value of static salt
	 */
	public static function getStaticSalt()
	{
		$configArray = require(__DIR__ . "/../config.php");
		return $configArray['STATIC_SALT'];
	}



	/**
	 * To create hash of a string using dynamic and static salt.
	 * @param string $pass			password in plain-text
	 * @param string $dynamicSalt		dynamic salt
	 * @param string $algo			The algorithm used to calculate hash
	 * @return string			final hash
	 */
	protected static function hashPassword($pass, $dynamicSalt = "", $algo = "")
	{
		if ($algo == "")
			$algo = BasicPasswordManagement::$hashAlgo;

		return hash($algo, strtolower($dynamicSalt . $pass . BasicPasswordManagement::getStaticSalt()));
	}



	/**
	 * To calculate hash of given password and then to check its equality against the old password's hash.
	 * @param string $newPassword		The given password in plain-text
	 * @param string $oldHash		The old hash
	 * @param string $oldSalt		The old dynamic salt used to create the old hash
	 * @param string $oldAlgo		The old algo used to create the hash
	 * @return boolean			True if new hash and old hash match. False otherwise
	 */
	protected static function validatePassword($newPassword, $oldHash, $oldSalt, $oldAlgo)
	{
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $oldSalt, $oldAlgo);

		if ($newHash === $oldHash)
			return TRUE;
		else
			return FALSE;
	}



	/**
	 * To calculate entropy of a string.
	 * @param string $string	The string whose entropy is to be calculated
	 * @return float		The entropy of the string
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
	 * To check if the string has ordered characters i.e. characters in strings are consecutive - such as "abcd". Also checks for reverse patterns such as "dcba".
	 * @param string $string	String in which we have to check for presence of ordered characters
	 * @param int $length		Minimum length of pattern to be qualified as ordered. e.g. String "abc" is not ordered if the length is 4 because it takes a minimum of 4 characters in consecutive orders to mark the string as ordered. Thus, the string "abcd" is an ordered character of length 4. Similarly "xyz" is ordered character of length 3 and "uvwxyz" is ordered character of length 6
	 * @return boolean		Returns true if ordered characters are found. False otherwise
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

		return \preg_match('#(.)(.\1){' . ($length - 1) . '}#', $str) == true;
	}



	/**
	 * To check if the string has keyboard ordered characters i.e. strings such as "qwert". Also checks for reverse patterns such as "rewq".
	 * @param string $string	String in which we have to check for presence of ordered characters
	 * @param int $length		Minimum length of pattern to be qualified as ordered. e.g. String "qwe" is not ordered if the length is 4 because it takes a minimum of 4 characters in consecutive orders to mark the string as ordered. Thus, the string "qwer" is an ordered character of length 4. Similarly "asd" is ordered character of length 3 and "zxcvbn" is ordered character of length 6
	 * @return boolean		Returns true if ordered characters are found. False otherwise
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

		return \preg_match('#(..)(..\1){' . ($length - 1) . '}#', $str) == true;
	}



	/**
	 * To check if the string is a phone-number.
	 * @param string $string	The string to be checked
	 * @return boolean		Returns true if the string is a phone number. False otherwise
	 */
	public static function isPhoneNumber($string)	//there are many cases for a legitimate phone number such as various area codes, strings in phone numbers, dashes in between numbers, etc. Hence not all possible combinations were taken into account.
	{
		//If the string contains only numbers and the length of the string is between 6 and 13, it is possibly a phone number.
		preg_match_all ("/^(\+)?\d{6,13}$/i", $string, $matches);	//checks for a '+' sign infront of string which may be present. Then checks for digits.

		if (count($matches[0]) >= 1)
			return TRUE;
		else
			return FALSE;
	}



	/**
	 * To check if the string contains a phone-number.
	 * @param string $string	The string to be checked
	 * @return boolean		Returns true if the string contains a phone number. False otherwise
	 */
	public static function containsPhoneNumber($string)	//there are many cases for a legitimate phone number such as various area codes, strings in phone numbers, dashes in between numbers, etc. Hence not all possible combinations were taken into account.
	{
		//If the string contains continous numbers of length beteen 6 and 13, then it is possible that the string contains a phone-number pattern. e.g. owasp+91917817
		preg_match_all ("/(\+)?\d{6,13}/i", $string, $matches);		//checks for a '+' sign infront of string which may be present. Then checks for digits.

		if (count($matches[0]) >= 1)
			return TRUE;
		else
			return FALSE;
	}



	/**
	 * To check if the string is a date.
	 * @param string $string	The string to be checked
	 * @return boolean		Returns true if the string is a date. False otherwise
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
	 * @param String $string	The string to be checked
	 * @return boolean		Returns true if the string contains a date. False otherwise
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
	 * @param string $string	The string to be checked
	 * @return boolean		Returns true if the string contains double words. False otherwise
	 */
	public static function containDoubledWords($string)
	{
		return (preg_match('/(.{3,})\\1/', $string) == 1);
	}



	/**
	 * To check if the given string(Hay) contains another string (Needle) in it.
	 * @param string $hay		The bigger string that contains another string
	 * @param string $needle	The pattern to search for
	 * @return boolean		Returns true if the smaller string is found inside the bigger string. False otherwise
	 */
	public static function containsString($hay, $needle)	//used for checking if the password contains usernames, firstname, lastname etc. Usually a password must not contain anything related to the user.
	{
		preg_match_all("/(" . $needle . ")/i", $hay, $matches);

		if (count($matches[0]) >= 1)
			return TRUE;
		else
			return FALSE;
	}



	/**
	 * To calculate the strength of a given string. The value lies between 0 and 1; 1 being the strongest.
	 * @param string $RawPassword	The string whose strength is to be calculated
	 * @return float		Strength of the string
	 */
	public static function strength($RawPassword)
	{
		$score = 0;

		//initial score is the entropy of the password
		$entropy = self::Entropy($RawPassword);
		$score += $entropy/4;	//maximum entropy is 8

		//check for common patters
		$ordered =		self::hasOrderedCharacters($RawPassword, strlen($RawPassword)/2);
		$fullyOrdered =		self::hasOrderedCharacters($RawPassword, strlen($RawPassword));
		$hasKeyboardOrder =	self::hasKeyboardOrderedCharacters($RawPassword,strlen($RawPassword)/2);
		$keyboardOrdered =	self::hasKeyboardOrderedCharacters($RawPassword,strlen($RawPassword));

		//If the whole password is ordered
		if ($fullyOrdered)
			$score*=.1;

		//If half the password is ordered
		elseif ($ordered)
			$score*=.5;

		//If the whole password is keyboard ordered
		if ($keyboardOrdered)
			$score*=.15;

		//If half the password is keyboard ordered
		elseif ($hasKeyboardOrder)
			$score*=.5;

		//If the whole password is a date
		if (self::isDate( $RawPassword))
			$score*=.2;

		//If the password contains a date
		elseif (self::containsDate( $RawPassword))
			$score*=.5;

		//If the whole password is a phone number
		if (self::isPhoneNumber( $RawPassword))
			$score*=.5;

		//If the password contains a phone number
		elseif (self::containsPhoneNumber( $RawPassword))
			$score*=.9;

		//If the password contains a double word
		if (self::containDoubledWords( $RawPassword))
			$score*=.3;

		//check for variety of character types
		preg_match_all ("/\d/i", $RawPassword, $matches);	//password contains digits
		$numbers = count($matches[0]) >= 1;

		preg_match_all ("/[a-z]/", $RawPassword, $matches);	//password contains lowercase alphabets
		$lowers = count($matches[0]) >= 1;

		preg_match_all ("/[A-Z]/", $RawPassword, $matches);	//password contains uppercase alphabets
		$uppers = count($matches[0]) >= 1;

		preg_match_all ("/[^A-z0-9]/", $RawPassword, $matches);	//password contains special characters
		$others = count($matches[0]) >= 1;

		//calculate score of the password after checking type of characters present
		$setMultiplier = ($others + $uppers + $lowers + $numbers)/4;

		//calculate score of the password after checking the type of characters present and the type of patterns present
		$score = $score/2 + $score/2*$setMultiplier;

		return min(1, max(0, $score));	//return the final score

	}



	/**
	 * To generate a random string of specified strength.
	 * @param float $Security	The desired strength of the string
	 * @return String		string that is of desired strength
	 */
	public static function generate($Security=.5)
	{
		$MaxLen=20;

		if ($Security > .3)
			$UseNumbers = true;	//can use digits.
		else
			$UseNumbers = false;

		if ($Security > .5)
			$UseUpper = true;		//can use upper case letters.
		else
			$UseUpper = false;

		if ($Security > .9)
			$UseSymbols = true;	//can use special symbols such as %, &, # etc.
		else
			$UseSymbols = false;


		$Length = max($Security*$MaxLen, 4);

		$chars = 'abcdefghijklmnopqrstuvwxyz';

		if ($UseUpper)		//If allowed to use uppercase
			$chars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		if ($UseNumbers)	//If allowed to use digits
			$chars .= "0123456789";

		if ($UseSymbols)	//If allowed to use special characters
			$chars .= "!@#$%^&*()_+-=?.,";

		$Pass="";

		//$char contains the string that has all the letters we can use in a password.

		//The loop pics a character from $char in random and adds that character to the final $pass variable.
		for ($i=0; $i<$Length; ++$i)
			$Pass .= $chars[rand(0, strlen($chars)-1)];

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
class WrongPasswordException extends UserException {}			//The password provided for the existing user is not correct.
class UserExistsException extends UserException {}			//User already exists in the database.
class UserNotExistsException extends UserException {}			//User does not exists in the database.
class UserLocked extends UserException {}				//User account is locked.
class UserAccountInactive extends UserException {}			//User account is inactive.
class UserIDInvalid extends UserException {}			// Invalid User ID ( It could be null, empty, it's length outside limit, use forbidden chars)



class User extends BasicPasswordManagement
{



	/**
	 * ID of the user.
	 * @var string
	 */
	protected $userID = null;



	/**
	 * Primary email of the user.
	 * @var string
	 */
	protected $primaryEmail = null;



	/**
	 * Hashing algorithm used for this user.
	 * @var string
	 */
	protected $hashAlgorithm = null;



	/**
	 * Hash of the user password.
	 * @var string
	 */
	private $hashedPassword = "";



	/**
	 * Dynamic salt used in creating the hash of the password.
	 * @var string
	 */
	private $dynamicSalt = "";



	/**
	 * Time after which a password must expire i.e. the password needs to be updated.
	 * @var int
	 */
	public static $passwordExpiryTime = 15552000;	//approx 6 months.



	/**
	 * Maximum time after which the user must re-login.
	 * @var int
	 */
	public static $rememberMeExpiryTime = 2592000;	//approx 1 month.


	/**
	 * Minimum number of chars allowed for UserID, should not exceed the table definition
	 * @var int
	 */
	public static $minUserIDNChars = 4;



	/**
	 * Maximum number of chars allowed for UserID, should match table definition
	 * @var int
	 */
	public static $maxUserIDNChars = 32;

	/**
	 * To create an object for a new user.
	 * @param string $id		The desired ID of the user
	 * @param string $pass		The desired password of the user
	 * @param string $pemail	The desired email of the user
	 * @throws UserExistsException	Will be thrown if the user already exists in the DB
	 * @throws UserIDInvalid	Will be thrown if the user ID Invalid ( It could be null, empty, it's length outside limit, use forbidden chars)
	 */
	public static function newUserObject($id, $pass, $pemail)
	{
		$obj = new User();	//create a new user object

		if (!User::isUserIDValid($id))
		    throw new UserIDInvalid("ERROR: User ID is invalid.");

		$obj->userID = $id;		//set userID
		$obj->primaryEmail = $pemail;	//set primary email

		$time = time();

		//calculate the hash of the password.
		$obj->dynamicSalt = hash(BasicPasswordManagement::$hashAlgo, randstr(128));
		$obj->hashedPassword = BasicPasswordManagement::hashPassword($pass, $obj->dynamicSalt, BasicPasswordManagement::$hashAlgo);
		$obj->hashAlgorithm = BasicPasswordManagement::$hashAlgo;

		$count = SQL("INSERT INTO USER (`USERID`, `P_EMAIL`, `ACCOUNT_CREATED`, `HASH`, `DATE_CREATED`, `ALGO`, `DYNAMIC_SALT`) VALUES (?, ?, ?, ?, ?, ?, ?)", array($obj->userID, $obj->primaryEmail, $time, $obj->hashedPassword, $time, BasicPasswordManagement::$hashAlgo, $obj->dynamicSalt));

		//If the user is already present in the database, then a duplicate won't be created and no rows will be affected. Hence 0 will be returned.
		if ($count == 0)
			throw new UserExistsException("ERROR: This User already exists in the DB.");
	}



	/**
	 * To get the object of an existing user.
	 * @param string $id		The id of the user
	 * @param string $pass		The password of the user
	 * @return \phpsec\User		The object of the user that enables them to use other functions
	 * @throws UserNotExistsException	Will be thrown if no user is found with the given ID
	 * @throws WrongPasswordException	Will be thrown if the given password does not matches the old password stored in the DB
	 */
	public static function existingUserObject($id, $pass)
	{
		$obj = new User();

		$result = SQL("SELECT `P_EMAIL`, `HASH`, `ALGO`, `DYNAMIC_SALT` FROM USER WHERE `USERID` = ?", array($id));

		//If no record is returned for this user, then this user does not exist in the system.
		if (count($result) != 1)
			throw new UserNotExistsException("ERROR: User Not found.");

		//validate the given password with that stored in the DB.
		if ( ! BasicPasswordManagement::validatePassword( $pass, $result[0]['HASH'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']))
			throw new WrongPasswordException("ERROR: Wrong Password.");

		//check if the user account is locked
		if (User::isLocked($id))
		{
			throw new UserLocked("ERROR: The account is locked!");
		}

		//check if the user account is inactive
		if (User::isInactive($id))
		{
			throw new UserAccountInactive("ERROR: The account is inactive. Please activate your account.");
		}

		//If all goes right, then set the local variables and return the user object.
		$obj->userID = $id;
		$obj->primaryEmail = $result[0]['P_EMAIL'];
		$obj->dynamicSalt = $result[0]['DYNAMIC_SALT'];
		$obj->hashedPassword = $result[0]['HASH'];
		$obj->hashAlgorithm = $result[0]['ALGO'];

		return $obj;
	}



	/**
	 * Function to provide userObject forcefully (i.e. without password).
	 * @param string $userID		The id of the user
	 * @return \phpsec\User			The object of the user that enables them to use other functions
	 * @throws UserNotExistsException	Will be thrown if no user is found with the given ID
	 */
	public static function forceLogin($id)
	{
		$obj = new User();

		$result = SQL("SELECT `P_EMAIL`, `HASH`, `ALGO`, `DYNAMIC_SALT` FROM USER WHERE `USERID` = ?", array($id));

		//If no record is returned for this user, then this user does not exist in the system.
		if (count($result) != 1)
			throw new UserNotExistsException("ERROR: User Not found.");

		$obj->userID = $id;
		$obj->primaryEmail = $result[0]['P_EMAIL'];
		$obj->dynamicSalt = $result[0]['DYNAMIC_SALT'];
		$obj->hashedPassword = $result[0]['HASH'];
		$obj->hashAlgorithm = $result[0]['ALGO'];

		return $obj;
	}



	/**
	 * To get the date when the user account was created. The value returned is the UNIX timestamp.
	 * @return int
	 */
	public function getAccountCreationDate()
	{
		$result = SQL("SELECT `ACCOUNT_CREATED` FROM USER WHERE USERID = ?", array($this->userID));
		return $result[0]['ACCOUNT_CREATED'];
	}



	/**
	 * To get the userID of the current User.
	 * @return string
	 */
	public function getUserID()
	{
		return $this->userID;
	}



	/**
	 * To get the primary email of the user.
	 * @param string $userID		The id of the user whose email is required
	 * @return string | boolean		Returns the email of the user if the user is found. False otherwise
	 */
	public static function getPrimaryEmail($userID)
	{
		$result = SQL("SELECT `P_EMAIL` FROM USER WHERE USERID = ?", array($userID));

		if (count($result) == 1)
		{
			return $result[0]['P_EMAIL'];
		}

		return FALSE;
	}



	/**
	 * Function to return the userID that is associated with the provided email.
	 * @param string $email
	 * @return boolean	Returns the userID associated with the email. If MULTIPLE USERID or NO userID is associated, then returns FALSE
	 */
	public static function getUserIDFromEmail($email)
	{
		$result = SQL("SELECT USERID FROM USER WHERE `P_EMAIL` = ?", array($email));

		if (count($result) == 1)
		{
			return $result[0]['USERID'];
		}

		return FALSE;
	}



	/**
	 * To verify if a given string is the correct password that is stored in the DB for the current user.
	 * @param string $password	The password that is to be checked against the one stored in DB
	 * @return boolean		Returns True if the passwords match. False otherwise
	 */
	public function verifyPassword($password)
	{
		return BasicPasswordManagement::validatePassword($password, $this->hashedPassword, $this->dynamicSalt, $this->hashAlgorithm);
	}



	/**
	 * Function to reset the password for the current user.
	 * @param string $oldPassword		The old password of the user
	 * @param string $newPassword		The new desired password of the user
	 * @return boolean			Returns true if the password is reset successfully
	 * @throws WrongPasswordException	Throws if the old password does not matches the one stored in the DB
	 */
	public function resetPassword($oldPassword, $newPassword)
	{
		//If given password ($oldPassword) is not matched with the one stored in the DB.
		if (! BasicPasswordManagement::validatePassword( $oldPassword, $this->hashedPassword, $this->dynamicSalt, $this->hashAlgorithm))
			throw new WrongPasswordException("ERROR: Wrong Password provided!!");

		//create a new dynamic salt.
		$this->dynamicSalt = hash(BasicPasswordManagement::$hashAlgo, randstr(128));

		//create the hash of the new password.
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $this->dynamicSalt, BasicPasswordManagement::$hashAlgo);

		//update the old password with the new password.
		SQL("UPDATE USER SET `HASH` = ?, `DATE_CREATED` = ?, `DYNAMIC_SALT` = ?, `ALGO` = ? WHERE `USERID` = ?", array($newHash, time(), $this->dynamicSalt, BasicPasswordManagement::$hashAlgo, $this->userID));

		$this->hashedPassword = $newHash;
		$this->hashAlgorithm = BasicPasswordManagement::$hashAlgo;

		return TRUE;
	}



	/**
	 * Function to force to change the password, even when the user has not provided the old password for verification. Used with "forgot password controller".
	 * If the user forgets his password, they need to be validated using their primary email. Once that is done, the user would like to keep a new password. This function will help there to keep a new password.
	 * @param string $newPassword
	 * @return boolean	Returns TRUE when the password has been changed successfully
	 */
	public function forceResetPassword($newPassword)
	{
		//create a new dynamic salt.
		$this->dynamicSalt = hash(BasicPasswordManagement::$hashAlgo, randstr(128));

		//create the hash of the new password.
		$newHash = BasicPasswordManagement::hashPassword($newPassword, $this->dynamicSalt, BasicPasswordManagement::$hashAlgo);

		//update the old password with the new password.
		SQL("UPDATE USER SET `HASH` = ?, `DATE_CREATED` = ?, `DYNAMIC_SALT` = ?, `ALGO` = ? WHERE `USERID` = ?", array($newHash, time(), $this->dynamicSalt, BasicPasswordManagement::$hashAlgo, $this->userID));

		$this->hashedPassword = $newHash;
		$this->hashAlgorithm = BasicPasswordManagement::$hashAlgo;

		return TRUE;
	}



	/**
	 * To delete the current user.
	 * @return boolean		Returns True if the user is deleted successfully
	 */
	public function deleteUser()
	{
		//Delete user Data from from Password Table.
		SQL("DELETE FROM PASSWORD WHERE USERID = ?", array($this->userID));

		//Delete user Data from from User Table.
		SQL("DELETE FROM USER WHERE USERID = ?", array($this->userID));

		return TRUE;
	}



	/**
	 * To check if the password has aged. i.e. if the time has passed after which the password must be changed.
	 * @return boolean	Returns TRUE if the password HAS AGED. False if the password has NOT AGED
	 */
	public function isPasswordExpired()
	{
		$result = SQL("SELECT `DATE_CREATED` FROM USER WHERE `USERID` = ?", array($this->userID));

		$currentTime = time();

		if ( ($currentTime - $result[0]['DATE_CREATED'])  > User::$passwordExpiryTime)
			return TRUE;
		else
			return FALSE;
	}



	/**
	 * Function to lock the user account.
	 */
	public static function lockAccount($userID)
	{
		SQL("UPDATE USER SET LOCKED = ? WHERE USERID = ?", array(1, $userID));
	}



	/**
	 * Function to unlock the user account.
	 */
	public static function unlockAccount($userID)
	{
		SQL("UPDATE USER SET LOCKED = ? WHERE USERID = ?", array(0, $userID));
	}



	/**
	 * Function to check if the user account is locked or not.
	 * @param string $userID	The id of the user whose account status is being checked
	 * @return boolean		Returns True if the account is locked. False otherwise
	 */
	public static function isLocked($userID)
	{
		$result = SQL("SELECT LOCKED FROM USER WHERE USERID = ?", array($userID));

		if (count($result) == 1)
		{
			if ($result[0]['LOCKED'] == 1)
			{
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Function to activate the account
	 */
	public static function activateAccount($userID)
	{
		SQL("UPDATE USER SET INACTIVE = ? WHERE USERID = ?", array(0, $userID));
	}



	/**
	 * Function to deactivate the account
	 */
	public function deactivateAccount()
	{
		SQL("UPDATE USER SET INACTIVE = ? WHERE USERID = ?", array(1, $this->userID));
	}



	/**
	 * Function to check if the user's account is inactive or not.
	 * @param string $userID		The id of the user whose account status is being checked
	 * @return boolean		Returns True if the account is inactive. False otherwise
	 */
	public static function isInactive($userID)
	{
		$result = SQL("SELECT INACTIVE FROM USER WHERE USERID = ?", array($userID));

		if (count($result) == 1)
		{
			if ($result[0]['INACTIVE'] == 1)
			{
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Function to enable "Remember Me" functionality.
	 * @param boolean $secure	//If set, the cookies will only set for HTTPS connections
	 * @param boolean $httpOnly	//If set, the cookies will only be accessible via HTTP Methods and not via Javascript and other means
	 * @return boolean		//Returns true if the function is enabled successfully
	 */
	public static function enableRememberMe($userID, $secure = TRUE, $httpOnly = TRUE)
	{
		$authID = randstr(128);	//create a new authentication token

		SQL("INSERT INTO `AUTH_TOKENS` (`AUTH_ID`, `USERID`, `DATE_CREATED`) VALUES (?, ?, ?)", array($authID, $userID, time()));

		//store the newly created session into the user cookie.
		if ($secure && $httpOnly)
			\setcookie("AUTHID", $authID, time() + User::$rememberMeExpiryTime, null, null, TRUE, TRUE);
		elseif (!$secure && !$httpOnly)
			\setcookie("AUTHID", $authID, time() + User::$rememberMeExpiryTime, null, null, FALSE, FALSE);
		elseif ($secure && !$httpOnly)
			\setcookie("AUTHID", $authID, time() + User::$rememberMeExpiryTime, null, null, TRUE, FALSE);
		elseif (!$secure && $httpOnly)
			\setcookie("AUTHID", $authID, time() + User::$rememberMeExpiryTime, null, null, FALSE, TRUE);

		return TRUE;
	}



	/**
	 * Function to check for AUTH token validity.
	 * @return boolean	Return the userID related to the token if the AUTH token is valid. False otherwise
	 */
	public static function checkRememberMe()
	{
		if (isset($_COOKIE['AUTHID']))
		{
			//get the given AUTH token from the DB.
			$result = SQL("SELECT * FROM `AUTH_TOKENS` WHERE `AUTH_ID` = ?", array($_COOKIE['AUTHID']));

			//If the AUTH token is found in DB
			if (count($result) == 1)
			{
				$currentTime = time();

				//If cookie time has expired, then delete the cookie from the DB and the user's browser.
				if ( ($currentTime - $result[0]['DATE_CREATED']) >= User::$rememberMeExpiryTime)
				{
					User::deleteAuthenticationToken();
					return FALSE;
				}
				else	//The AUTH token is correct and valid. Hence, return the userID related to this AUTH token
					return $result[0]['USERID'];
			}
			else	//If this AUTH token is not found in DB, then erase the cookie from the client's machine and return FALSE
			{
				\setcookie("AUTHID", "");
				return FALSE;
			}
		}
		else	//If the user is unable to provide a AUTH token, then return FALSE
			return FALSE;
	}



	/**
	 * Function to delete the current user authentication token from the DB and user cookies
	 */
	public static function deleteAuthenticationToken()
	{
		if (isset($_COOKIE['AUTHID']))
		{
			SQL("DELETE FROM `AUTH_TOKENS` WHERE `AUTH_ID` = ?", array($_COOKIE['AUTHID']));
			\setcookie("AUTHID", "");
		}
	}



	/**
	 * Function to check if a userID is elegible for use.
	 * Not allowed: null, empty, use char other than (A-Z 0-9 or _ @ . -),outside length limit
	 * @return boolean	Return True if UserID is ellegible. False otherwise
	 */
	public static function isUserIDValid($userID)
	{
		if ($userID == null || strlen($userID) < User::$minUserIDNChars || strlen($userID) > User::$maxUserIDNChars)
			return FALSE;

		return preg_match("/^[a-z0-9A-Z_@.-]*$/", $userID) == 1;
	}
}

?>
