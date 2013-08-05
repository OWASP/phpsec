<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once __DIR__ . "/../../../libs/logs/logger.php";

class LoggerSyslogTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Function to test the storage of logs in SYSLOG.
	 */
	public function testCreation()
	{
		$myLogger = new Logger(__DIR__ . "/../../../libs/logs/media/default_syslog_config.php"); //create a handler to store the logs. Provide that logger with a configuration file.

		$myLogger->log("This is the first message", "WARNING", "LOW"); //store this log.
		$myLogger->log("This is the second message"); //store this log.

		//You can see the results in the console.
		//You can also check using this command in your shell:
		//grep -R "This is the first message" /var/log
		//You should see entries containing message "This is the first message"
	}
}
