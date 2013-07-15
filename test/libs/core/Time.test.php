<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once "../../../libs/core/time.php";



class TimeTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * Function to check if correct system time is returned or not.
	 */
	public function testSYSTime()
	{
		$this->assertTrue(\time() == time("SYS"));
	}
	
	
	
	/**
	 * Function to check if current time is returned or not. (2 Cases).
	 */
	public function testCurrTime()
	{
		$firstTest = (time("CURR") == \time());		//First case - SInce user have not set a different time, so the system time must be returned.
		
		$secondTest = (time("SET", \time()+1000) == \time() + 1000);	//second case - The user have specified a time, so the user specified current time must be returned.
		
		$this->assertTrue($firstTest && $secondTest);
	}
	
	
	
	/**
	 * Function to test if time is currectly set or not.
	 */
	public function testSetTime()
	{
		$this->assertTrue(time("SET", \time()+1000) == \time()+1000);
	}
	
	
	
	/**
	 * Function to check if time is correctly resetted or not.
	 */
	public function testResetTime()
	{
		$this->assertTrue(time("RESET") == \time());
	}
	
	
	
	/**
	 * Function to check if time is correctly moved or not.
	 */
	public function testMoveTime()
	{
		$this->assertTrue(time("MOV", 1000) == \time() - 1000);
	}
}
