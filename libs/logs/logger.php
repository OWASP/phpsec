<?php
namespace phpsec;


/**
 * Parent Exception
 */
class LogException extends \Exception {}


/**
 * Child Exceptions
 */
class MediaNotSupported extends LogException {}
class UnsupportedConfigFile extends LogException {}



class Logger
{


	/**
	 * This variable will store the object of the class for appropriate storage media. e.g. if logs are supposed to go in DB, then this varible will hold object of class DBLogs, if file is chosed, then this variable will hold object of class FILE
	 * @var	Object This varible will store object of the media classes such as DB or File
	 */
	protected $handler = null;


	/**
	 * This variable is used to store the user configuration for logs in form of an array.
	 * @var Array
	 */
	protected $config = null;



	/**
	 * The constructor function takes the path to configuration files and imports that file. Then it extracts the type of storage medium is desired and make necessary arrangements to store logs in that media.
	 * @param String $pathToConfigFile	This requires path to your configuration file.
	 * @throws MediaNotSupported
	 */
	public function __construct( $pathToConfigFile = "media/default_db_config.php" )
	{
		//get the configuration file.
		$this->config = Logger::getConfig($pathToConfigFile);

		//extract the desired storage medium.
		$storageType = $this->config["MEDIA"];
		if ( file_exists( __DIR__ . "/media/" . $storageType . ".php" ) )	//IF that storage medium is supported, then
		{
			require_once (__DIR__ . "/media/" . $storageType . ".php");	//get that storage medium.

			$storageType = "\phpsec\\" . $storageType;
			$this->handler = new $storageType($this->config);	//get an obhect of that storage medim.
		}
		else
			throw new MediaNotSupported("ERROR: This media is not supported yet. Please try a different medium of storage.");	//If storage medium is not supported, then throw an exception.
	}



	/**
	 * Function to pass the messages to the appropriate storage class to be generate logs.
	 */
	public function log()
	{
		$args = func_get_args();	//get all the messages such as logMessage, type of log etc, and send them to the appropriate storage media class for storage.

		$this->handler->log($args);
	}



	/**
	 * Fuction to get the configuration file from the path given by the user.
	 * @param String $pathToConfigFile	//needs path to your configuration file.
	 * @return array
	 * @throws UnsupportedConfigFile
	 */
	public static function getConfig($pathToConfigFile)
	{
		//get the extension of the file.
		$a = explode('.', $pathToConfigFile);
		$extension = array_pop($a);	//extract the extension.
		$ex = strtolower($extension);

		//if the extension is PHP, then call that configuration file to extract the configurations.
		if ($ex == "php")
		{
			return include($pathToConfigFile);
		}
		else
			throw new UnsupportedConfigFile("ERROR: Configuration file of this type is not supported yet!");	//throw an exception if the configuration file is not recognized.
	}
}

?>