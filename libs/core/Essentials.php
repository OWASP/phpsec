<?php
namespace phpsec;

class Essentials
{
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