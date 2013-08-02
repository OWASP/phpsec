<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once __DIR__ . "/../../../libs/logs/logger.php";


class LoggerFileTest extends \PHPUnit_Framework_TestCase
{


	/**
	 * Function to test the storage of logs in file.
	 */
	public function testCreation()
	{
		$myLogger = new Logger(__DIR__."/testFileConfig.php"); //create a handler to store the logs. Provide that logger with a configuration file.

		$myLogger->log("This is the first message", "WARNING", "LOW"); //store this log.
		$myLogger->log("This is the second message"); //store this log.

		if (file_exists("myfile.php")) {
			$this->assertTrue(TRUE);
		} else {
			$this->assertTrue(FALSE);
		}
	}
}

?>