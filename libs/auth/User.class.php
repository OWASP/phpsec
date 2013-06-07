<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Time.class.php');
require_once (__DIR__ . '/../core/Rand.class.php');

/**
 * SANITIZE ALL INPUTS. TO-DO FOR LATER.    <-------------  ATTENTION HERE ------------------------>
 */


class BasicPasswordManagement
{
	/**
	 * Changing this salt in application, would invalidate all previous passwords, because their static salt would change.
	 * @var type 
	 */
	public static $_staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
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
	
	//taken from http://stackoverflow.com/questions/3198005/help-with-the-calculation-and-usefulness-of-password-entropy
	public static function Entropy($string)
	{
		$h=0;
		$size = strlen($string);
		foreach (count_chars($string, 1) as $v)
		{
			$p = $v/$size;
			$h -= $p*log($p)/log(2);
		}
		return $h;
	}
	
	//taken from jframework
	public static function hasOrderedCharacters($string, $length) {
		$length=(int)$length;
		$i = 0;
		$j = strlen($string);
		$str = implode('', array_map(function($m) use (&$i, &$j) {
			return chr((ord($m[0]) + $j--) % 256) . chr((ord($m[0]) + $i++) % 256);
		}, str_split($string, 1)));
		return preg_match('#(.)(.\1){' . ($length - 1) . '}#', $str)==true;
	}
	
	//taken from jframework
	public static function hasKeyboardOrderedCharacters($string, $length) {
		$length=(int)$length;
		$i = 0;
		$j = strlen($string);
		$str = implode('', array_map(function($m) use (&$i, &$j) {
			$keyboardSet="1234567890qwertyuiopasdfghjklzxcvbnm";
			return ((strpos($keyboardSet,$m[0]) + $j--) ) . ((strpos($keyboardSet,$m[0]) + $i++) );
		}, str_split($string, 1)));
		return preg_match('#(..)(..\1){' . ($length - 1) . '}#', $str)==true;
	}
	
	public static function isPhoneNumber($string)	//there are many cases that phone numbers can be arranged. Hence not all possible combinations were taken into account.
	{
		preg_match_all ("/^(\+)?\d{6,13}$/i", $string, $matches);
		
		if (count($matches[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	public static function containsPhoneNumber($string)	//there are many cases that phone numbers can be arranged. Hence not all possible combinations were taken into account.
	{
		preg_match_all ("/(\+)?\d{6,13}/i", $string, $matches);
		
		if (count($matches[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	public static function isDate($string)
	{
		preg_match_all ("/^(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches1);
		preg_match_all ("/^(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches2);
		
		preg_match_all ("/^(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches3);
		preg_match_all ("/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)$/i", $string, $matches4);
		
		preg_match_all ("/^((19|20)?\d\d)[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])$/i", $string, $matches5);
		preg_match_all ("/^((19|20)?\d\d)[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])$/i", $string, $matches6);
		
		if (count($matches1[0])>=1 || count($matches2[0])>=1 || count($matches3[0])>=1 || count($matches4[0])>=1 || count($matches5[0])>=1 || count($matches6[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	public static function containsDate($string)
	{
		preg_match_all ("/(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches1);
		preg_match_all ("/(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches2);
		
		preg_match_all ("/(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches3);
		preg_match_all ("/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])[.\-\/\s]?((19|20)?\d\d)/i", $string, $matches4);
		
		preg_match_all ("/((19|20)?\d\d)[.\-\/\s]?(0?[1-9]|1[012])[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])/i", $string, $matches5);
		preg_match_all ("/((19|20)?\d\d)[.\-\/\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[.\-\/\s]?(0?[1-9]|[12][0-9]|3[01])/i", $string, $matches6);
		
		if (count($matches1[0])>=1 || count($matches2[0])>=1 || count($matches3[0])>=1 || count($matches4[0])>=1 || count($matches5[0])>=1 || count($matches6[0])>=1)
			return TRUE;
		else
			return FALSE;
	}
	
	public static function containDoubledWords($string)	//such as crabcrab, stopstop, treetree, passpass, etc.
	{
		$firstHalf = substr($string, 0, (strlen($string) / 2));
		$secondHalf = substr($string, (strlen($string) / 2), strlen($string));
		
		if ($firstHalf == $secondHalf)
			return TRUE;
		else
			return FALSE;
	}
	
	public static function containsString($hay, $needle)	//used for checking for usernames, firstname, lastname etc.
	{
		preg_match_all("/(" . $needle . ")/i", $hay, $matches);
		
		if (count($matches[0]) >= 1)
			return TRUE;
		else
			return FALSE;
	}
	
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
	
	public static function generate($Security=.5)
	{
		$MaxLen=20;
		
		if ($Security>.3)
			$UseNumbers=true;
		else
			$UseNumbers=false;
		
		if ($Security>.5)
			$UseUpper=true;
		else
			$UseUpper=false;
		
		if ($Security>.9)
			$UseSymbols=true;
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

class User
{
	private $_handler = null;
	
	private $_userID = null;
	
	private $_hashedPassword = "";
	private $_dynamicSalt = "";
	
	public static function newUserObject($dbConn, $id, $pass, $email, $staticSalt = "")
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

				$query = "INSERT INTO USER (`USERID`, `HASH`, `DATE_CREATED`, `TOTAL_SESSIONS`, `EMAIL`, `ALGO`, `DYNAMIC_SALT`, `STATIC_SALT`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
				$args = array("{$obj->_userID}", $obj->_hashedPassword, $time, 0, $email, BasicPasswordManagement::$hashAlgo, $obj->_dynamicSalt, BasicPasswordManagement::$_staticSalt);
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

				BasicPasswordManagement::$_staticSalt = $result[0]['STATIC_SALT'];
				
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
		BasicPasswordManagement::$_staticSalt = $newSalt;
		
		try
		{
			$query = "INSERT INTO STATIC_SALT (`STATICSALT`) VALUES (?)";
			$args = array(BasicPasswordManagement::$_staticSalt);
			$count = $this->_handler -> SQL($query, $args);
			
			if ($count == 0)
				throw new SaltAlreadyPresentInDB("This static-salt is already present in the DB. Please choose a different salt.");
		}
		catch(\Exception $e)
		{
			throw $e;
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