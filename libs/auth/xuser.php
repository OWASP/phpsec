<?php
namespace phpsec;

class IncorrectTimeFormatException extends \Exception {}

class XUser extends User
{
	protected $firstName = NULL;
	protected $lastName = NULL;
	protected $primaryEmail = NULL;
	protected $secondaryEmail = NULL;
	protected $dob = NULL;
	protected $securityAnswer1 = NULL;
	protected $securityAnswer2 = NULL;
	protected static $minAge = 378684000;	//12 years.
	
	public function __construct($userObj, $primaryEmail)
	{
		$this->userID = $userObj->getUserID();
		$this->primaryEmail = $primaryEmail;
		
		SQL("INSERT INTO XUSER (`USERID`, `P_EMAIL`) VALUES (?, ?)", array($this->userID, $this->primaryEmail));
	}
	
	public function setName($firstName, $lastName)
	{
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		
		SQL("UPDATE XUSER SET `FIRST_NAME` = ?, `LAST_NAME` = ? WHERE USERID = ?", array($this->firstName, $this->lastName, $this->userID));
	}
	
	public function setSecondaryEmail($secondaryEmail)
	{
		$this->secondaryEmail = $secondaryEmail;
		
		SQL("UPDATE XUSER SET `S_EMAIL` = ? WHERE USERID = ?", array($this->secondaryEmail, $this->userID));
	}
	
	public function setDOB($dob)
	{
		$dob = (int)$dob;
		
		if ( (strlen($dob."") != 10) || ($dob > time()) )
		{
			throw new IncorrectTimeFormatException("ERROR: Incorrect time format is passed. Only Unix-timestamps are accepted.");
		}
		
		$this->dob = $dob;
		
		SQL("UPDATE XUSER SET `DOB` = ? WHERE USERID = ?", array($this->dob, $this->userID));
	}
	
	public function setSecurityAnswers($answer1, $answer2)
	{
		$dynamicSalt = randstr(64);
		
		$this->securityAnswer1 = BasicPasswordManagement::hashPassword($answer1, $dynamicSalt, BasicPasswordManagement::$hashAlgo);
		$this->securityAnswer2 = BasicPasswordManagement::hashPassword($answer2, $dynamicSalt, BasicPasswordManagement::$hashAlgo);
		
		SQL("UPDATE XUSER SET `DYNAMIC_SALT` = ?, `SECURITY1` = ?, `SECURITY2` = ?, `ALGO` = ? WHERE USERID = ?", array($dynamicSalt, $this->securityAnswer1, $this->securityAnswer2, BasicPasswordManagement::$hashAlgo, $this->userID));
	}
	
	public static function checkSecurityAnswer1($userID, $userAnswer)
	{
		$result = SQL("SELECT `SECURITY1`, `DYNAMIC_SALT`, `ALGO` FROM XUSER WHERE USERID = ?", array($userID));
		
		if (count($result) == 1)
		{
			return BasicPasswordManagement::validatePassword($userAnswer, $result[0]['SECURITY1'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']);
		}
		
		return FALSE;
	}
	
	public static function checkSecurityAnswer2($userID, $userAnswer)
	{
		$result = SQL("SELECT `SECURITY2`, `DYNAMIC_SALT`, `ALGO` FROM XUSER WHERE USERID = ?", array($userID));
		
		if (count($result) == 1)
		{
			return User::validatePassword($userAnswer, $result[0]['SECURITY2'], $result[0]['DYNAMIC_SALT'], $result[0]['ALGO']);
		}
		
		return FALSE;
	}
	
	public function ageCheck()
	{
		$result = SQL("SELECT `DOB` FROM XUSER WHERE USERID = ?", array($this->userID));
		
		if (count($result) == 1)
		{
			if ( (time() - $result[0]['DOB']) < XUser::$minAge )
				return FALSE;
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function deleteXUser()
	{
		SQL("DELETE FROM XUSER WHERE USERID = ?", array($this->userID));
	}
}