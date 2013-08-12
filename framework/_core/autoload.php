<?php
namespace phpsec\framework;
class Autoloader 
{
	static function phpsecPath()
	{
		return realpath(__DIR__."/../../libs/");
	}
	static $PhpsecArray=array(
			
			"AdvancedPasswordManagement"	=>	"auth/adv_password",
			"BasicPasswordManagement"		=>	"auth/user",
			"UserManagement"				=>	"auth/usermanagement",
			
			"ErrorHandler"					=>	"core/error",
	
			"DatabaseManager"				=>	"db/dbmanager",
			
			"HttpRequest"					=>	"http/http",
			"DownloadManager"				=>	"http/download",
			"TaintedString"					=>	"http/tainted",
	
			"Logger"						=>	"logs/logger",
			
			"Session"						=>	"session/session",
	);
	static $FrameworkArray=array(
			
			
	);
	static function autoload($ClassnameWithNamespace)
	{
		if (substr($ClassnameWithNamespace,0,7)!="phpsec\\") return false;
		
		$Classname=substr($ClassnameWithNamespace,7);
		if (substr($Classname,0,10)=="framework\\")
		{
			$Classname=substr($Classname,10);
			if (isset(self::$FrameworkArray[$Classname]))
			{
				require_once __DIR__."/".self::$FrameworkArray[$Classname].".php";
				return true;
			}
		}
		if (isset(self::$PhpsecArray[$Classname]))
		{
			require_once self::phpsecPath()."/".self::$PhpsecArray[$Classname].".php";
			return true;
		}
			
		return false;
	}
}
spl_autoload_register(__NAMESPACE__."\\Autoloader::autoload");
