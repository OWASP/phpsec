<?php

class UserSignupController extends phpsec\framework\DefaultController
{
	function Handle()
	{
		try
		{
			if ( (isset($_POST['user'])) && (isset($_POST['pass'])) && (isset($_POST['repass'])) )
			{
				if ( phpsec\UserManagement::userExists($_POST['user']) )
				{
					$this->error = "ERROR: This username is not available. Please select a different one.";
					//Keep a config file that will tell if username suggestion must be enabled or not and then if enabled, suggest a new username.
					//Then call the appropriate view to reload the page so that user can enter the new username.
				}

				if ( ($_POST['pass']) !== ($_POST['repass']) )
				{
					$this->error = "ERROR: Password fields do not match!";
				}

				if (phpsec\BasicPasswordManagement::$passwordStrength > phpsec\BasicPasswordManagement::strength($_POST['pass']))
				{
					$this->error = "ERROR: This password is too weak. Please choose a different password. A good password contains a-z, A-Z, 0-9, & special characters.";
					//Keep a config file that will tell if password suggestion must be enabled or not and then if enabled, suggest a new password calling the generatePassword method.
					//Then call the appropriate view to reload the page so that user can enter the new password.
				}

				//call account-activation controller and create new user object.
			}
			else
			{
				$this->error = "ERROR: Empty fields are not allowed.";
				//show the login page again here so that users can enter their credentials again.
			}
		}
		catch (Exception $e)
		{
			$this->error = $e->getMessage();
			//call appropriate view.
			
			//You can also call here individual errors such as UserExistsException for a more precise action.
		}
	}
}

?>