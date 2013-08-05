<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once __DIR__ . "/../../../libs/core/time.php";


class TimeTest extends \PHPUnit_Framework_TestCase
{

	protected function setUp()
	{
		// Other tests will affect the time.
		time('RESET');
	}

	/**
	 * Function to check if correct system time is returned or not.
	 */
	public function testSYSTime()
	{
		$this->assertSame(\time(), time("SYS"));
	}

	/**
	 * Function to check if current time is returned or not. (2 Cases).
	 */
	public function testCurrAndSetTime()
	{
		//If user have not set a different time, so the system time must be returned.
		$this->assertSame(\time(), time("CURR"));

		// Set arbitrary time value and let it be returned
		$this->assertSame(12789, time("SET", 12789));

		// Now CURR should return the set time as current.
		$this->assertSame(12789, time("CURR"));

		sleep(1);
		// Now CURR should return the set time plus 1 second as current.
		$this->assertSame(12790, time("CURR"));

	}

	/**
	 * Function to check if time is correctly reset or not.
	 */
	public function testResetTime()
	{
		$this->assertSame(\time(), time("RESET"));
	}

	/**
	 * Function to check if time is correctly moved or not.
	 */
	public function testMoveTime()
	{
		$this->assertSame(\time() - 1000, time("MOV", 1000));
	}
}
