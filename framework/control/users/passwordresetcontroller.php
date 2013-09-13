<?php

class PasswordResetController extends phpsec\framework\DefaultController
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
			//first check if the user is logged-in. We can check this by calling one of its method. If the user is not logged-in, then exception would be thrown.
			try
			{
				$this->userObj->getUserID();
			}
			catch (Exception $e)
			{
				$this->error = "ERROR: User not logged-in!";
				//call the userlogincontroller
			}

			if ( (isset($_POST['_x_oldpass'])) && ($_POST['pass']) && ($_POST['repass']) )
			{
				if (phpsec\BasicPasswordManagement::$passwordStrength > phpsec\BasicPasswordManagement::strength($_POST['pass']))
				{
					$this->error = "ERROR: This password is too weak. Please choose a different password. A good password contains a-z, A-Z, 0-9, & special characters.";
					//Keep a config file that will tell if password suggestion must be enabled or not and then if enabled, suggest a new password calling the generatePassword method.
					//Then call the appropriate view to reload the page so that user can enter the new password.
				}

				try
				{
					$this->userObj->resetPassword($_POST['_x_oldpass'], $_POST['pass']);
				}
				catch (phpsec\WrongPasswordException $e)
				{
					try
					{
						$advancedAnalysis = new phpsec\AdvancedPasswordManagement($this->userObj->getUserID(), $_POST['_x_oldpass'], TRUE);
					}
					catch (phpsec\BruteForceAttackDetectedException $ex)
					{
						$this->error = $ex->getMessage();
						//What to do if Brute Force Detected ?
					}

					$this->error = $e->getMessage();

					//show the same page again here so that users can enter their credentials again.
				}
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