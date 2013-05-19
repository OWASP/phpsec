<?php

class ExceptionHandler extends Exception
{
	public function error($classObj, $msg)
	{
		$error = "An error occured in class: " . get_class($classObj) . ".\n" . $msg;
		return $error;
	}
}


class SessionExceptions extends ExceptionHandler
{
	
}


class UserExceptions extends ExceptionHandler
{
	public static function userIDNotFound()
	{
		return error(new User(), "userID is not set. Make an object of class User inside User.class.php and run function \"setUserID\" to resolve this issue.");
	}
}

?>