<?php
namespace phpsec;



/**
 * Required Files.
 */
require_once "../../tools/scanner.php";



class ScannerTest extends \PHPUnit_Framework_TestCase
{
	
	
	/**
	 * Function to test scanning of files.
	 */
	public function testScanning()
	{
		try
		{
			//start the scan.
			$errors = Scanner::scanDir("../../tools");
			
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
