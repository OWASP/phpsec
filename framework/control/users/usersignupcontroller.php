<?php

class UserSignupController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		try
		{
			if ((isset($_POST['submit'])))
			{
				if ( (isset($_POST['user'])) && ((isset($_POST['email']))) && (isset($_POST['pass'])) && (isset($_POST['repass'])) )
				{
					$config = require_once (__DIR__ . "/../../config/config.php");

					if ( phpsec\UserManagement::userExists($_POST['user']) )
					{
						$this->error .= "ERROR: This username is not available. Please select a different one." . "<BR>";

						if ($config['USERNAME_SUGGESTION'] === "ON")
						{
							do
							{
								$suggestedUsername = \phpsec\BasicPasswordManagement::generate (.1);
							}
							while (phpsec\UserManagement::userExists($suggestedUsername));

							$this->info .= "This username is available: " . $suggestedUsername . "<BR>";
						}

						return require_once(__DIR__ . "/../../view/default/user/signup.php");
					}

					if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})$/',$_POST['email']))
					{
						$this->error .= "Invalid email address." . "<BR>";
						return require_once(__DIR__ . "/../../view/default/user/signup.php");
					}

					if ( ($_POST['pass']) !== ($_POST['repass']) )
					{
						$this->error .= "ERROR: Password fields do not match!" . "<BR>";
						return require_once(__DIR__ . "/../../view/default/user/signup.php");
					}

					if (phpsec\BasicPasswordManagement::$passwordStrength > phpsec\BasicPasswordManagement::strength($_POST['pass']))
					{
						$this->error .= "ERROR: This password is too weak. Please choose a different password. A good password contains a-z, A-Z, 0-9, & special characters." . "<BR>";

						if ($config['PASSWORD_SUGGESTION'] === "ON")
						{
							$this->info .= "This password is strong: " . substr(\phpsec\BasicPasswordManagement::generate(1), 0, 8) . "<BR>";
						}

						return require_once(__DIR__ . "/../../view/default/user/signup.php");
					}

					phpsec\UserManagement::createUser($_POST['user'], $_POST['pass'], $_POST['email']);

					$nextLocation = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/temppass?user=" . $_POST['user'] . "&mode=activation" . "&email=" . $_POST['email'];
					header("Location: {$nextLocation}");
				}
				else
				{
					$this->error .= "ERROR: Empty fields are not allowed." . "<BR>";
					return require_once(__DIR__ . "/../../view/default/user/signup.php");
				}
			}
		}
		catch (Exception $e)
		{
			$this->error .= $e->getMessage() . "<BR>";
			return require_once(__DIR__ . "/../../view/default/user/signup.php");
		}

		return require_once(__DIR__ . "/../../view/default/user/signup.php");
	}
}

?>