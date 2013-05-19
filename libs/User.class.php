<?php

class User
{
	private $_userID = null;
	private $_firstName = null;
	private $_lastName = null;
	
	/**
	 * LATER REMOVE THIS FUNCTION. THE USERID WOULD BE SET BY A DIFFERENT METHOD.
	 * @param type $id
	 */
	public function setUserID($id)
	{
		$this -> _userID = $id;
	}
	
	public function getUserID()
	{
		if ($this -> _userID == null)
		{
			throw new Exception("User ID not set.");
		}
		else
			return $this -> _userID;
	}
}

?>