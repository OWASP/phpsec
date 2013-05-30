<?php

	namespace phpsec;

	class HttpRequestException extends \Exception {}
	class HttpRequestInsecureParameterException extends HttpRequestException {}

	/**
	 * HttpRequest class
	 * Wrapper class to securely process HTTP request parameters
	 */
	
	class HttpRequest
	{
		/**
		 * Returns IP address of client
		 * @return  string IP
		 */
		static function IP ()
		{
			if (php_sapi_name() === "cli")
				return '127.0.0.1';
			return isset ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
		}

		/**
		 * Returns $_SERVER parameter if secure and throws exception if not.
		 * Can be forced to return parameter irrespective of security.
		 * @param  string $param [$_SERVER index]
		 * @param  boolean $force [to override security check]
		 * @return string Parameter
		 */
		static function getParameter ($param, $force = false)
		{
			if ($force === true)
				return $_SERVER[$param];
			else
			{
				if (substr($param,0,4) === 'HTTP')
					throw new HttpRequestInsecureParameterException("\$_SERVER['$param'] is an insecure parameter and shouldn't be trusted for sensitive transactions.");
				else
					return $_SERVER[$param];
			}
		}

	}

?>