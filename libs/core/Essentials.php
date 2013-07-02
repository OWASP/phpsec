<?php
namespace phpsec;

class Essentials
{
	
	/**
	 * To check if a connection is HTTPS or not.
	 * @return boolean
	 */
	public static function checkHTTPS()
	{
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
		{
			return TRUE;
		}
		else
			return FALSE;
	}
}

?>