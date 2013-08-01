<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/logs/logger.php";



class LoggerTest extends \PHPUnit_Framework_TestCase
{
	
	
	/**
	 * Function to test the storage of logs in file.
	 */
	public function testCreation()
	{
		try
		{
			$myLogger = new Logger("testFileConfig.php");	//create a handler to store the logs. Provide that logger with a configuration file.
			
			$myLogger->log("This is the first message", "WARNING", "LOW");	//store this log.
			$myLogger->log("This is the second message");	//store this log.
			
			if(  file_exists( "myfile.php" ) )
				$this->assertTrue( TRUE );
			else
				$this->assertTrue( FALSE );
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}
	}
}

?>