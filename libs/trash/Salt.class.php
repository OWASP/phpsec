<?php
namespace phpsec;

require_once __DIR__ . '/../core/Rand.class.php';

class Salt
{
	private static $_staticSalt = "7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	private static $_dynamicSalt = "";
	
	public static function getStaticSalt()
	{
		return Salt::$_staticSalt;
	}
	
	public static function getDynamiSalt()
	{
		return Salt::$_dynamicSalt;
	}
	
	public static function make($username, $rawPassword, $dynamicSalt='')
	{
		if ($dynamicSalt == '')
			Salt::$_dynamicSalt = hash("sha512",Rand::generateRandom(32));
		else
			Salt::$_dynamicSalt = $dynamicSalt;
		
		return strtolower($username . Salt::$_dynamicSalt . $rawPassword . Salt::$_staticSalt);
	}
}

?>