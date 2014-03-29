<?php

class UserIndexController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
		header("Pragma: no-cache");

		$userSession = new phpsec\Session();
		$sessionID = $userSession->existingSession();

		if ($sessionID != FALSE)
		{
			$userID = \phpsec\Session::getUserIDFromSessionID($sessionID);
			return require_once (__DIR__ . "/../../view/default/user/index.php");
		}
		else
		{
			$newLocation = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/home";
			header("Location: {$newLocation}");
		}
	}
}

?>
