<?php

class UserLogoutController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		try
		{
			$userSession = new phpsec\Session();
			$sessionID = $userSession->existingSession();

			if ($sessionID != FALSE)
			{
				$userID = \phpsec\Session::getUserIDFromSessionID($sessionID);
				$userObj = phpsec\UserManagement::forceLogIn($userID);
				phpsec\UserManagement::logOut($userObj);
			}
			else
			{
				phpsec\User::deleteAuthenticationToken();
			}

			$this->info .= "You are now logged out." . "<BR>";
			$nextURL = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/home";
			header("Location: {$nextURL}");
		}
		catch (Exception $e)
		{
			$this->error .= $e->getMessage() . "<BR>";
			$lastURL = $_SERVER['HTTP_REFERER'];
			header("Location: {$lastURL}");
		}
	}
}

?>