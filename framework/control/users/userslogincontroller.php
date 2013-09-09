<?php

class UsersLoginController extends phpsec\framework\DefaultController
{
	function Handle()
	{
		try
		{
			phpsec\UserManagement::logIn($_POST['user'], $_POST['pass']);
		}
		catch (\phpsec\UserNotExistsException $e)
		{
			$file=__DIR__."/../../view/error.php";
			if (realpath($file))
				require $file;
		}
		catch (phpsec\WrongPasswordException $e)
		{
			$file=__DIR__."/../../view/error.php";
			if (realpath($file))
				require $file;
		}
	}
}


?>