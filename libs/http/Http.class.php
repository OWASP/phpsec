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

		/**
		 * Returns the current URL
		 * @return  string URL
		 */
		static function URL ()
		{
			if (php_sapi_name() === "cli")
				return NULL;
			return (self::Protocol()."://".self::ServerName().self::PortReadable().self::RequestURI());
		}

		/**
		 * Returns name of the server host
		 * @return  string ServerName
		 */
		static function ServerName ()
		{
			return isset ($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
		}

		/**
		 * Returns protocol of the client connection, HTTP/HTTPS
		 * @return string Protocol
		 */
		static function Protocol ()
		{
			if (php_sapi_name() === "cli")
				return 'cli';
			$x = (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : '';
			if ($x == "off" or $x == "")
				return "http";
			else
				return "https";
		}

		/**
		 * Returns port of client connection
		 * @return string Port
		 */
		static function Port ()
		{
			if (php_sapi_name() === "cli")
				return NULL;
			return isset ($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : "";
		}

		static function PortReadable ()
		{
			$port = self::Port();
			if ($port=="80" && strtolower(self::Protocol())=="http")
				$port="";
			else if ($port=="443" && strtolower(self::Protocol())=="https")
				$port="";
			else
				$port=":".$port;
		}

		/**
		 * Returns the URI for current script
		 * @return  string RequestURI
		 */
		static function RequestURI ()
		{
			if (php_sapi_name() === "cli")
				return NULL;
			return isset ($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		}

	}

?>