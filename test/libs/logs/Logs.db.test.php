<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/logs/logger.php";

class LoggerTest extends \PHPUnit_Framework_TestCase
{
	public function testCreation()
	{
		try
		{
			$myLogger = new Logger();
			$myLogger->log("This is the first message", __FILE__, "WARNING", "LOW");
			$myLogger->log("This is the second message", __FILE__);
		}
		catch (\Exception $e)
		{
			echo $e->getMessage() . "\n";
			echo $e->getLine() . "\n";
			echo $e->getFile();
		}
	}
}

?>