<?php
namespace phpsec;

class Time
{
	
	/**
	 * To specify if the current time is correct time or artificial time.
	 * @var boolean
	 */
	public static $realTime = true;
	
	
	/**
	 * To keep the current time.
	 * @var int
	 */
	private static $currentTime = 0;
	
	
	
	/**
	 * To return the time either artificial or correct.
	 * @return int
	 */
	public static function time()
	{
		if(Time::$realTime == true)
		{
			Time::resetTime ();	//If correct time is set, then reset the clock. This will reset the clock to correct and current time.
		}
		else
		{
			Time::$currentTime += 10;	//If you have to fake time, then for each call, increase 'n' seconds from the fake time.
		}
		
		return Time::$currentTime;
	}
	
	
	/**
	 * Function to set the time to a specified time.
	 * @param int $time
	 */
	public static function setTime($time)
	{
		Time::$currentTime = (int)$time;
	}
	
	
	/**
	 * Function to reset the time. This will reset the current time to current and correct time.
	 */
	public static function resetTime()
	{
		Time::$currentTime = \time();
	}
	
	
	/**
	 * Function to decrease the clock time to the specified time.
	 * @param int $difference
	 */
	public static function moveTime($difference)
	{
		Time::$currentTime = (int)\time() - (int)$difference;
	}
}

?>