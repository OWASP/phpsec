<?php
namespace phpsec;
require_once __DIR__."/functions.php";
class ErrorHandlerAlreadySetException extends \Exception {}
class ErrorHandlerNotSetException extends \Exception {}
class ErrorHandler
{
	/**
	 * Holds the previous error_reporting state so that we can get back there
	 * @var integer
	 */
	static private $backupErrorReportingState=null;

	static protected $isShutdownRegistered=null;
	/**
	 * Sets the phpsec error handler as error handler
	 * @throws ErrorHandlerAlreadySetException
	 */
	static public function enable()
	{
		if (self::isActive())
			throw new ErrorHandlerAlreadySetException("This function shouldn't be called twice.");
		self::$backupErrorReportingState=error_reporting();
		set_error_handler(__NAMESPACE__."\\ErrorHandler::_errorToException");

		if (!self::$isShutdownRegistered)
		{
			//no matter how many times enable is called, add shutdown function once
			register_shutdown_function(__NAMESPACE__."\\ErrorHandler::_shutdown");
			self::$isShutdownRegistered=true;
		}
		error_reporting(0);
	}
	/**
	 * Unsets phpsec error handler, back to the previous one
	 * @throws ErrorHandlerNotSetException
	 */
	static public function disable()
	{
		if (!self::isActive())
			throw new ErrorHandlerNotSetException("This function should be callde after setErrorHandler.");
		error_reporting(self::$backupErrorReportingState);
		restore_error_handler ();
	}

	/**
	 * Tells whether or not error handler is active
	 * @return boolean
	 */
	static public function isActive()
	{
		return self::$backupErrorReportingState!==null;
	}
	/**
	 * This is registered as a shutdown function to catch fatal errors
	 * Do not call this directly.
	 */
	public static function _shutdown()
	{
		//if error handler is not enabled, just ignore
		if (!self::isActive()) return;

		$e=error_get_last();
		if ($e===null) return; //no errors yet!
		$type=$e['type'];

		//only say fatal error, if the last error has been fatal!
		if ($type==E_ERROR or $type==E_CORE_ERROR or $type==E_PARSE or $type==E_COMPILE_ERROR or $type==E_USER_ERROR)
		{
			if (strpos($e['message'],"ErrorException")===false) //exceptions automatically have filename in their message
				echof ("Fatal Error ?: ? [?:<strong>?</strong>]",$e['type'],$e['message'],$e['file'],$e['line']);
			else
				echo_br("Fatal Error {$e['type']}: {$e['message']}");
			exit(1);
		}
	}

	/**
	 * Converts a php error to a php exception (ErrorException)
	 * You don't need to call this directly.
	 * @param type $errMsg
	 * @param type $errNo
	 * @param type $errFile
	 * @param type $errLine
	 * @throws ErrorException
	 */
	public static function _errorToException( $errNo ,$errMsg, $errFile = null, $errLine = null,$errContext=null)
	{
		throw new \ErrorException($errMsg, $errNo, 0, $errFile, $errLine);
	}

	/**
	 * Dumps an exception in readable format
	 * @param Exception $e
	 */
	public static function dump(Exception $e)
	{
		echof ($e->getTraceAsString());
	}

}
