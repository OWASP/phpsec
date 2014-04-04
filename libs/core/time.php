<?php
namespace phpsec;


/**
 * To return the time either artificial or correct.
 * @param boolean $realTime
 * @return int
 */
function time($mode = "CURR", $givenTime = 0)
{
	if (strtoupper($mode) == "CURR")	//If mode is "CURR", return the correct user specified time.
		return Time::time ();
	elseif (strtoupper($mode) == "SET")	//If mode is "SET", set the current time as specified by user.
		return Time::setTime ( $givenTime );
	elseif (strtoupper($mode) == "RESET")	//If mode is "RESET", reset the time to the correct system time.
		return Time::resetTime ();
	elseif (strtoupper($mode) == "MOV")	//If mode is "MOV", move the clock backward to the difference in time specified by the user. E.g. to move time backward 10 seconds, you will write phpsec\time("MOV", 10);
		return Time::moveTime ($givenTime);
	elseif (strtoupper($mode) == "SYS")	//If mode is "SYS", return the correct system time.
		return \time();
	else
		return 0;			//If none of the mode matches, return 0.
}



class Time
{

	/**
	 * To keep the user specified time.
	 * @var int
	 */
	private static $currentTime = 0;


	/**
	 * To keep the last time when System time was changed.
	 * @var int
	 */
	private static $lastFalseTimeSet = 0;



	/**
	 * To return the current user specified time.
	 * @return int
	 */
	public static function time()
	{
                $timeMoved = abs(\time() - Time::$lastFalseTimeSet);	//check how much the clock has moved since the last false time was set.
		Time::$currentTime += $timeMoved;			//add that moveded time to the current user specified time.
		Time::$lastFalseTimeSet = \time();

		return Time::$currentTime;
	}


	/**
	 * Function to set the time to a specified time.
	 * @param int $time
	 */
	public static function setTime($time)
	{
		Time::$lastFalseTimeSet = \time();
		Time::$currentTime = (int)$time;

		return Time::$currentTime;
	}


	/**
	 * Function to reset the time to the current system time.
	 */
	public static function resetTime()
	{
		Time::$currentTime = 0;
		Time::$lastFalseTimeSet = 0;

		return \time();
	}


	/**
	 * Function to decrease the clock time to the specified time.
	 * @param int $difference
	 */
	public static function moveTime($difference)
	{
		Time::$lastFalseTimeSet = \time();
		Time::$currentTime = (int)\time() - (int)$difference;

		return Time::$currentTime;
	}
}

?>