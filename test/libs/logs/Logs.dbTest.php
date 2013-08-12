<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once __DIR__ . "/../../../libs/logs/logger.php";
require_once(__DIR__ . "/../testconfig.php");


class LoggerDbTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Varible to keep the object of class \phpsec\Logger.
	 * @var \phpsec\Logger
	 */
	private $myLogger = NULL;


	/**
	 * Function to be run at the start of each test.
	 */
	public function setUp()
	{
		$this->myLogger = new Logger(); //create an instance of the /phpsec/Logger class.
	}


	/**
	 * Function to test the storage of logs in DB.
	 */
	public function testCreation()
	{
		$this->myLogger->log("This is the first message", "WARNING", "LOW"); //store this log.
		$this->myLogger->log("This is the second message"); //store this log.

		$result = SQL("SELECT * FROM LOGS"); //get how many records are there in the log DB.

		$this->assertCount(2, $result); // Should have two log entries.
	}
}
