<?php
namespace phpsec;

class ErrorHandler
{
	public function __construct()
	{
		set_error_handler(ErrorHandler::handler);
	}
	
	/**
	 * check the parameter for errors.
	 * @param type $errMsg
	 * @param type $errNo
	 * @param type $errFile
	 * @param type $errLine
	 * @throws ErrorException
	 */
	public static function handler($errMsg, $errNo = null, $errFile = null, $errLine = null)
	{
		throw new ErrorException($errMsg, $errNo, 0, $errFile, $errLine);
	}
}

//core exceptions
class DBException extends \Exception {}

class IntegerNotFoundException extends \Exception {}



//derived exceptions
class DBConnectionNotFoundException extends DBException {}
class DBQueryNotExecutedError extends DBException {}

?>