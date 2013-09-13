<?php

class UserLogoutController extends phpsec\framework\DefaultController
{
	protected $userObj = NULL;
	
	function __construct($userObject)
	{
		$this->userObj = $userObject;
	}
	
	function Handle()
	{
		try
		{
			phpsec\UserManagement::logOut($this->userObj);
			//call appropriate view for the login page.
		}
		catch (Exception $e)
		{
			$this->error = $e->getMessage();
			//call appropriate view.
			
			//You can also call here individual errors for a more precise action.
		}
	}
}

?>