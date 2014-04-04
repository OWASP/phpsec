<?php

class TempPassController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		try
		{
			if ( (isset($_GET['user'])) && ($_GET['user'] != "") && (isset($_GET['verification'])) && (($_GET['verification'] != "")) && (($_GET['mode'] === 'temppass') || ($_GET['mode'] === 'activation')) )
			{
				if (phpsec\AdvancedPasswordManagement::tempPassword($_GET['user'], $_GET['verification']))
				{
					if ($_GET['mode'] === 'temppass')
					{
						$userSession = new phpsec\Session();
						$userSessionID = $userSession->newSession($_GET['user']);

						$nextLocation = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/requestnewpassword";
						header("Location: {$nextLocation}");
					}
					else if ($_GET['mode'] === 'activation')
					{
						\phpsec\User::activateAccount($_GET['user']);
						$this->info .= "Your account <b>" . $_GET['user'] . "</b> is now activated." . "<BR>";
						require_once (__DIR__ . "/../../view/default/user/temppass.php");
					}
				}
				else
				{
					$this->error .= "ERROR: This validation token does not match our records!!!" . "<BR>";
					return require_once (__DIR__ . "/../../view/default/user/temppass.php");
				}
			}
			else if ( (isset($_GET['user'])) && ($_GET['user'] != "") && (isset($_GET['email'])) && ($_GET['email'] != "") && (($_GET['mode'] === 'temppass') || ($_GET['mode'] === 'activation')) )
			{
				$tempPass = phpsec\AdvancedPasswordManagement::tempPassword($_GET['user']);

				$message = "Please open the following link in order to complete the process:\n";
				$message .= \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/temppass?user=" . $_GET['user'] . "&mode=" . $_GET['mode'] ."&verification=" . $tempPass . "\n\n\n";
				$message .= "Sometimes the email ends up in the Spam folder. So also please check your spam folder in case you didn't receive the email.\n\n";
				$message .= "If you did nothing to get this email, just ignore it.\n";
				$message = wordwrap($message, 70, "\r\n");

				$send = \mail(	$_GET['email'],
						"Authentication Email",
						$message,
						"FROM: " . "rahul300chaudhary400@gmail.com"
						//"FROM: " . "admin@" . \phpsec\HttpRequest::Host() . "\r\n"
					    );

				if ( !$send )
				{
					$this->error .= "ERROR: Mail was not send!" . "<BR>";
				}

				return require_once (__DIR__ . "/../../view/default/user/temppass.php");
			}
			else
				return require_once (__DIR__ . "/../../view/default/404.php");
		}
		catch (Exception $e)
		{
			$this->error .= $e->getMessage() . "<BR>";
			return require_once (__DIR__ . "/../../view/default/user/temppass.php");
		}
	}
}