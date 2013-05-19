<?php

	/**
	* This is the configuration file for running the framework in production.
	* Copy this over to `server.php` in the same directory to run.
	* Returns an array of configuration options.
	*
	* @return array Configuration Options
	*/

	return array(

		/**
		 * MODE
		 * 
		 * This is an identifier for the framework's current mode of operation.
		 * 
		 */

		'MODE' => 'production',

		/**
		 * COOKIES_DOMAIN
		 *
		 * Determines the default HTTP cookie domain.
		 * 
		 */

		'COOKIES_DOMAIN' => '',

		/**
		 * COOKIES_SECURE
		 *
		 * Determines whether or not cookies are delivered only via HTTPS.
		 * 
		 */

		'COOKIES_SECURE' => true,

		/**
		 * COOKIES_HTTPONLY
		 *
		 * Determines whether or not the HttpOnly flag is set while delivering cookies.
		 * 
		 */

		'COOKIES_HTTPONLY' => true,

		/**
		 * DB_HOST
		 * DB_USER
		 * DB_PASS
		 * DB_NAME
		 *
		 * MySQL database credentials.
		 */

		'DB_HOST' => 'localhost',
		'DB_USER' => 'root',
		'DB_PASS' => '',
		'DB_NAME' => 'owasp'

	);

?>