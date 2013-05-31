<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Time.class.php');

class UserException extends \Exception {}

class DBHandlerForUserNotSetException extends UserException {}

class User
{
	private $_handler = null;
	
	private $_userID = null;
	private $_firstName = null;
	private $_lastName = null;
	
	public function __construct($dbConn, $id, $fname = '', $lname = '')
	{
		$this->_handler = $dbConn;
		
		if ($this->_handler == null)
		{
			throw new DBHandlerForUserNotSetException("<BR>ERROR: Connection to DB was not found.<BR>");
		}
		else
		{
			$this->_userID = $id;
			$this->_firstName = $fname;
			$this->_lastName = $lname;
			
			try
			{
				$time = Time::time();

				$query = "INSERT INTO USER (`USERID`, `DATE_CREATED`, `TOTAL_SESSIONS`, `FIRST_NAME`, `LAST_NAME`) VALUES (?, ?, ?, ?, ?)";
				$args = array("{$this->_userID}", $time, 0, "{$this->_firstName}", "{$this->_lastName}");
				$count = $this->_handler -> SQL($query, $args);
			}
			catch(\Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public function getUserID()
	{
		return $this->_userID;
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