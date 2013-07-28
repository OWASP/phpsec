<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/logs/logger.php";



class LoggerTest extends \PHPUnit_Framework_TestCase
{
	
	
	/**
	 * Function to test the storage of logs in SYSLOG.
	 */
	public function testCreation()
	{
		try
		{
			$myLogger = new Logger(__DIR__ . "/../../../libs/logs/media/default_syslog_config.php");	//create a handler to store the logs. Provide that logger with a configuration file.
			
			$myLogger->log("This is the first message", "WARNING", "LOW");	//store this log.
			$myLogger->log("This is the second message");	//store this log.
			
			$this->assertTrue( TRUE );	//You can see the results in the console.
		}
		catch (\Exception $e)
		{
			echo $e->getMessage() . "\n";
			echo $e->getLine() . "\n";
			echo $e->getFile() . "\n";
		}
	}
}

?>