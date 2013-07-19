<?php
namespace phpsec;

class LogException extends \Exception {}

class MediaNotSupported extends LogException {}
class UnsupportedConfigFile extends LogException {}

class Logger
{
	protected $handler = null;
	protected $config = null;
	
	public function __construct( $pathToConfigFile = "media/default_file_config.php" )
	{
		$this->config = Logger::getConfig($pathToConfigFile);
		
		$storageType = $this->config["media"];
		if ( file_exists( __DIR__ . "/media/" . $storageType . ".php" ) )
		{
			require_once (__DIR__ . "/media/" . $storageType . ".php");
			
			$storageType = "\phpsec\\" . $storageType;
			$this->handler = new $storageType($this->config);
		}
		else
			throw new MediaNotSupported("<BR>ERROR: This media is not supported yet. Please try a different medium of storage.<BR>");
	}
	
	public function log($message)
	{
		$this->handler->log($message);
	}
	
	public static function getConfig($pathToConfigFile)
	{
		$a = explode('.', $pathToConfigFile);
		$extension = array_pop($a);	//extract the extension.
		$ex = strtolower($extension);

		if ($ex == "php")
		{
			return include_once ($pathToConfigFile);
		}
		else if ($ex == "xml")
		{
			return;
		}
		else
			throw new UnsupportedConfigFile("<BR>ERROR: Configuration file of this type is not supported yet!<BR>");
	}
}

?>