<?php
namespace phpsec;

require_once (__DIR__ . '/../core/Time.class.php');
require_once (__DIR__ . '/../session/Session.class.php');

class UserException extends \Exception {}

class User extends DBException
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
			throw new DBConnectionNotFoundException("<BR>ERROR: Connection to DB was not found.<BR>");
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
				
				if ($count == 0)
					throw new DBQueryNotExecutedError("<BR>ERROR: Unable to insert new User data in DB.<BR>");
			}
			catch(IntegerNotFoundException $e)
			{
				throw $e;
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
			$session = new Session($this, $this->_handler);
			
			if( $session->destroyAllSessions() )
			{
				$query = "DELETE FROM USER WHERE USERID = ?";
				$args = array("{$this->_userID}");
				$count = $this->_handler -> SQL($query, $args);
			}
		}
		catch(NoUserFoundException $e)
		{
			throw $e;
		}
		catch(DBQueryNotExecutedError $e)
		{
			throw $e;
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}
}

?>