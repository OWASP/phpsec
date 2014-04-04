<?php

class RequestNewPasswordController extends phpsec\framework\DefaultController
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
					if ( isset($_POST['pass']) && ($_POST['pass'] != "") && isset($_POST['repass']) && ($_POST['repass'] != "") )
					{
						$config = require_once (__DIR__ . "/../../config/config.php");

						if (phpsec\BasicPasswordManagement::$passwordStrength > phpsec\BasicPasswordManagement::strength($_POST['pass']))
						{
							$this->error .= "ERROR: This password is too weak. Please choose a different password. A good password contains a-z, A-Z, 0-9, & special characters." . "<BR>";

							if ($config['PASSWORD_SUGGESTION'] === "ON")
							{
								$this->info .= "This password is strong: " . substr(\phpsec\BasicPasswordManagement::generate(1), 0, 8) . "<BR>";
							}

							return require_once (__DIR__ . "/../../view/default/user/newpassword.php");
						}

						if ($_POST['pass'] !== $_POST['repass'])
						{
							$this->error .= "Your Password and Re-Type Password fields do not match. Please enter the same password twice." . "<BR>";
							return require_once (__DIR__ . "/../../view/default/user/newpassword.php");
						}

						$userID = \phpsec\Session::getUserIDFromSessionID($sessionID);

						if ($userID !== FALSE)
						{
							$userObj = phpsec\UserManagement::forceLogIn($userID);
							if ($userObj->forceResetPassword($_POST['pass']))
								$this->info .= "Your Password has been changed successfully." . "<BR>";
							else
								$this->error .= "We encountered an error. Please re-try later!" . "<BR>";
						}
						else
						{
							$userSession->destroySession();
							$this->error .= "Your session seems to be invalid. Cannot proceed!!" . "<BR>";
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
				$this->error .= "Seems you should not be accessing this page!" . "<BR>";
				$newLocation = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/login";
				header("Location: {$newLocation}");
			}
		}
		catch (Exception $e)
		{
			$this->error .= $e->getMessage() . "<BR>";
		}

		return require_once (__DIR__ . "/../../view/default/user/newpassword.php");
	}
}

?>
