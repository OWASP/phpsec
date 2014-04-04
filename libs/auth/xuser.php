<?php
namespace phpsec;



class XUser extends User
{



	/**
	 * First name of the user
	 * @var string
	 */
	protected $firstName = NULL;



	/**
	 * Last name of the user
	 * @var string
	 */
	protected $lastName = NULL;



	/**
	 * Secondary email of the user
	 * @var string
	 */
	protected $secondaryEmail = NULL;



	/**
	 * Date of Birth of the user
	 * @var int
	 */
	protected $dob = NULL;



	/**
	 * Minimum age that is required for all users
	 * @var int
	 */
	protected static $minAge = 378684000;	//12 years.



	/**
	 * Constructor of this class.
	 * @param \phpsec\User $userObj		The object of class \phpsec\User
	 */
	public function __construct($userObj)
	{
		$this->userID = $userObj->getUserID();

		if (! XUser::isXUserExists($this->userID))	//If user's records are not present in the DB, then insert them
			SQL("INSERT INTO XUSER (`USERID`) VALUES (?)", array($this->userID));
	}



	/**
	 * To check if the user's record are present in the DB or not.
	 * @param string $userID	The userID of the user
	 * @return boolean		Returns true if the user is present. False otherwise
	 */
	protected static function isXUserExists($userID)
	{
		$result = SQL("SELECT USERID FROM XUSER WHERE USERID = ?", array($userID));
		return (count($result) == 1);
	}



	/**
	 * To set the first name and last name of the user
	 * @param string $firstName	The first name of the user
	 * @param string $lastName	The last name of the user
	 */
	public function setName($firstName, $lastName)
	{
		$this->firstName = $firstName;
		$this->lastName = $lastName;

		SQL("UPDATE XUSER SET `FIRST_NAME` = ?, `LAST_NAME` = ? WHERE USERID = ?", array($this->firstName, $this->lastName, $this->userID));
	}



	/**
	 * To set the secondary email of the user
	 * @param string $secondaryEmail	The secondary email of the user
	 */
	public function setSecondaryEmail($secondaryEmail)
	{
		$this->secondaryEmail = $secondaryEmail;

		SQL("UPDATE XUSER SET `S_EMAIL` = ? WHERE USERID = ?", array($this->secondaryEmail, $this->userID));
	}



	/**
	 * To set the DOB of the user
	 * @param int $dob	The DOB of the user
	 */
	public function setDOB($dob)
	{
		$dob = (int)$dob;

		if ( $dob < time() )	//The given DOB is in past because DOB's cant be in future
		{
			$this->dob = $dob;
			SQL("UPDATE XUSER SET `DOB` = ? WHERE USERID = ?", array($this->dob, $this->userID));
		}
	}



	/**
	 * TO check if the age of the user satisfies the age criteria
	 * @return boolean	Returns true if the age is greater than the minimum age. False otherwise
	 */
	public function ageCheck()
	{
		$result = SQL("SELECT `DOB` FROM XUSER WHERE USERID = ?", array($this->userID));

		if ( (time() - $result[0]['DOB']) < XUser::$minAge )
			return FALSE;

		return TRUE;
	}



	/**
	 * To delete the current user's record from the DB
	 */
	public function deleteXUser()
	{
		SQL("DELETE FROM XUSER WHERE USERID = ?", array(  $this->userID));
	}
}