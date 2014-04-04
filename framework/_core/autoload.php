<?php
namespace phpsec\framework;
/**
 * The framework autoloader
 * This class handles three types of autoloads,
 * first are phpsec libs files according to the $PhpsecArray
 * second are phpsec framework modules, according to $FrameworkArray
 * third are generic models, camelCased, which are located inside model folder
 *  e.g someNiceClass would be sought in model/some/nice/class.php
 *  e.g \Some\Namespac3\SomeNiceClass would be sought in model/Some/Namespac3/some/nice/class.php
 *
 * This also automatically loads phpsec core
 * @author abiusx
 *
 */
require_once Autoloader::phpsecPath()."/core/loader.php";
require_once (__DIR__ . "/../config/dbconnection.php");

class Autoloader
{
	static function phpsecPath()
	{
		return realpath(__DIR__."/../../libs/");
	}
	static $PhpsecArray=array(

			"AdvancedPasswordManagement"	=>	"auth/adv_password",
			"BasicPasswordManagement"	=>	"auth/user",
			"User"				=>	"auth/user",
			"UserManagement"		=>	"auth/usermanagement",
			"ErrorHandler"			=>	"core/error",
			"DatabaseManager"		=>	"db/dbmanager",
			"HttpRequest"			=>	"http/http",
			"DownloadManager"		=>	"http/download",
			"TaintedString"			=>	"http/tainted",
			"Logger"			=>	"logs/logger",
			"Session"			=>	"session/session",
	);
	static $FrameworkArray=array(
			"Controller"					=>	"base/control",
			"DefaultController"				=>	"base/control",

	);
	static function autoload($ClassnameWithNamespace)
	{
		if (substr($ClassnameWithNamespace,0,7)=="phpsec\\") //phpsec modules
		{
			$Classname=substr($ClassnameWithNamespace,7);
			if (substr($Classname,0,10)=="framework\\") //framework modules
			{
				$Classname=substr($Classname,10);
				if (isset(self::$FrameworkArray[$Classname]))
				{
					require_once __DIR__."/".self::$FrameworkArray[$Classname].".php";
					return true;
				}
			}
			if (isset(self::$PhpsecArray[$Classname])) //phpsec-lib modules
			{
				require_once self::phpsecPath()."/".self::$PhpsecArray[$Classname].".php";
				return true;
			}
		}
		return self::GenericAutoload($ClassnameWithNamespace);
	}

	/**
	 * This function autoloads models that have the same path as their classname,
	 * e.g SomeNiceModel would be in model/some/nice/model.php
	 * @param string $Classname
	 * @return boolean
	 */
	private static function GenericAutoload($ClassnameWithNamespace)
	{
		$Namespace=substr($ClassnameWithNamespace,0,strrpos($ClassnameWithNamespace,"\\"));
		$NamespaceParts=explode("\\",$Namespace);
		$Classname=substr($ClassnameWithNamespace,strlen($Namespace)+($Namespace?1:0));
		//separates words in a camelCase word (also CamelCase), a single uppercase letter counts as one. If don't want it, change * to +
		preg_match_all('/((?:^|[A-Z])[a-z_0-9]*)/',$Classname,$matches);
		if (!is_array($matches[0]))
			return false;
		$Path=implode("/",$NamespaceParts)."/".strtolower(implode("/",$matches[0]));
		$Path=realpath(__DIR__."/../model/")."/{$Path}.php";
		if (realpath($Path))
		{
			require_once $Path;
			return true;
		}
		return false;
	}
}
spl_autoload_register(__NAMESPACE__."\\Autoloader::autoload");
