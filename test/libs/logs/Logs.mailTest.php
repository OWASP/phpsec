<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once __DIR__ . "/../../../libs/logs/logger.php";

class LoggerMailTest extends \PHPUnit_Framework_TestCase
{


	/**
	 * Function to test the storage of logs in mail.
	 */
	public function testCreation()
	{
		$this->markTestSkipped('Mailing log results currently cannot be tested automatically.');

		$myLogger = new Logger(__DIR__ . "/../../../libs/logs/media/default_mail_config.php"); //create a handler to store the logs. Provide that logger with a configuration file.

		$myLogger->log("This is the first message", "WARNING", "LOW"); //store this log.
		$myLogger->log("This is the second message"); //store this log.

		//You should see two mails in your mailbox.
	}
}
