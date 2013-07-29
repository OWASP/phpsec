<?php
namespace phpsec;



/**
 * Required Files.
 */
require_once "../../../libs/scanner/scanner.php";



class LoggerTest extends \PHPUnit_Framework_TestCase
{
	
	
	/**
	 * Function to test scanning of files.
	 */
	public function testScanning()
	{
		try
		{
			//set the parent directory to scan.
			Scanner::$parentDirectory = "../../../libs/scanner";
			
			//start the scan.
			$errors = Scanner::startScan();
			
			//print the results.
			print_r($errors);
			
			//You can see the results in your screen.
			$this->assertTrue(TRUE);
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