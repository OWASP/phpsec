<?php

class UsersLoginController extends phpsec\framework\DefaultController
{
	function Handle()
	{
		try
		{
			$userID = phpsec\User::checkRememberMe();
		
			if (! $userID)
			{
				if ( (!isset($_POST['user'])) && (!isset($_POST['pass'])) )
				{
					//show login error.
				}

				$userID = $_POST['user'];
				try
				{
					$userObj = phpsec\UserManagement::logIn($_POST['user'], $_POST['pass']);
				}
				catch (phpsec\WrongPasswordException $e)
				{
					try
					{
						$advancedAnalysis = new phpsec\AdvancedPasswordManagement($_POST['user'], $_POST['pass'], TRUE);
					}
					catch (phpsec\BruteForceAttackDetectedException $ex)
					{
						$this->error = $ex->getMessage();
						//What to do if Brute Force Detected ?
					}
					
					$this->error = $e->getMessage();
					
					//show the login page again here so that users can enter their credentials again.
				}

				if ( (isset($_POST['remember-me'])) && ($_POST['remember-me'] == "on") )
				{
					if (phpsec\HttpRequest::isHTTPS())
					{
						phpsec\User::enableRememberMe($userID);
					}
					else
					{
						phpsec\User::enableRememberMe($userID, FALSE, TRUE);
					}
				}
			}

			$userSession = new phpsec\Session();

			if ($userSession->existingSession())
			{
				$userSessionID = $userSession->rollSession();
			}
			else
			{
				$userSessionID = $userSession->newSession($userID);
			}

			$userObj = phpsec\UserManagement::forceLogIn($userID);
			
			if (! $userObj->isPasswordExpired() )
			{
				//what next to do after successful login ?
			}
			else
			{
				$this->error = "ERROR: Its been too long since you have changed your password. For security reasons, please change your password.";
				//call reset-password controller.
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