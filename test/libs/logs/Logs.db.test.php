<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/logs/logger.php";

class LoggerTest extends \PHPUnit_Framework_TestCase
{
	private $myLogger = NULL;
	
	public function setUp()
	{
		$this->myLogger = new Logger();
	}
	
	public function testCreation()
	{
		try
		{
			$this->myLogger->log("This is the first message", __FILE__, "WARNING", "LOW");
			$this->myLogger->log("This is the second message", __FILE__);
			
			$this->assertTrue(TRUE);
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