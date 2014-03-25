<?php

class ForgotPasswordController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		try
		{
			if (isset($_POST['submit']))
			{
				if ( isset($_POST['email']) && ($_POST['email'] != "") )
				{
					$userID = phpsec\User::getUserIDFromEmail($_POST['email']);

					if ($userID !== FALSE)
					{
						$nextLocation = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/temppass?user=" . $userID . "&mode=temppass" . "&email=" . $_POST['email'];
						header("Location: {$nextLocation}");
					}
					else
						$this->error .= "This email ID is not registered in our DB. Please enter the email you provided at the time of sign-up. Alternatively it might happen that multiple accounts are associated with this Email ID. For the time being only 1 email account is supported per userID." . "<BR>";
				}
				else
				{
					$this->error .= "ERROR: Empty fields are not allowed." . "<BR>";
				}
			}
		}
		catch (Exception $e)
		{
			$this->error .= $e->getMessage() . "<BR>";
		}

		return require_once (__DIR__ . "/../../view/default/user/forgotpassword.php");
	}
}

?>