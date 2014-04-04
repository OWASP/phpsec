<?php

class PasswordResetController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		try
		{
			$userSession = new phpsec\Session();
			$sessionID = $userSession->existingSession();

			if ($sessionID != FALSE)
			{
				if (isset($_POST['submit']))
				{
					$userID = \phpsec\Session::getUserIDFromSessionID($sessionID);

					if ( (isset($_POST['_x_oldpass'])) && ($_POST['_x_oldpass'] != "") && (isset($_POST['pass'])) && ($_POST['pass'] != "") && (isset($_POST['repass'])) && ($_POST['repass'] != "") )
					{
						$config = require_once (__DIR__ . "/../../config/config.php");

						if (phpsec\BasicPasswordManagement::$passwordStrength > phpsec\BasicPasswordManagement::strength($_POST['pass']))
						{
							$this->error .= "ERROR: This password is too weak. Please choose a different password. A good password contains a-z, A-Z, 0-9, & special characters." . "<BR>";

							if ($config['PASSWORD_SUGGESTION'] === "ON")
							{
								$this->info .= "This password is strong: " . substr(\phpsec\BasicPasswordManagement::generate(1), 0, 8) . "<BR>";
							}

							return require_once(__DIR__ . "/../../view/default/user/passwordreset.php");
						}

						if ($_POST['pass'] !== $_POST['repass'])
						{
							$this->error .= "Your Password and Re-Type Password fields do not match. Please enter the same password twice." . "<BR>";
							return require_once(__DIR__ . "/../../view/default/user/passwordreset.php");
						}

						try
						{
							$userObj = phpsec\UserManagement::logIn($userID, $_POST['_x_oldpass']);
							$userObj->resetPassword($_POST['_x_oldpass'], $_POST['pass']);
							$this->info .= "Your password have been changed successfully." . "<BR>";
						}
						catch (phpsec\WrongPasswordException $e)
						{
							if ($config['BRUTE_FORCE_DETECTION'] === "ON")
							{
								try
								{
									new phpsec\AdvancedPasswordManagement($userID, $_POST['pass'], TRUE);
								}
								catch (phpsec\BruteForceAttackDetectedException $ex)
								{
									\phpsec\User::lockAccount($userID);
									$this->error .= "Brute Force Attack detected on this account. This account has now been locked. If its not your fault, then please contact the administrator." . "<BR>";
								}
							}

							$this->error .= "Your old password does not seems correct. Please enter your old password for verification." . "<BR>";
						}
					}
					else
					{
						$this->error .= "ERROR: Empty fields are not allowed." . "<BR>";
					}
				}
			}
			else
			{
				$this->error .= "You are not logged-in. Please login to complete the operation." . "<BR>";
				$newLocation = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/login";
				header("Location: {$newLocation}");
			}
		}
		catch (Exception $e)
		{
			$this->error .= $e->getMessage() . "<BR>";
		}

		return require_once (__DIR__ . "/../../view/default/user/passwordreset.php");
	}
}

?>