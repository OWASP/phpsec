<?php

class Time
{
	private static $_currentTime = 0;
	
	public static function getCurrentTime()
	{
		return Time::$_currentTime;
	}
	
	public static function setCurrentTime($time)
	{
		Time::$_currentTime = $time;
	}
	
	public static function setToSystemTime()
	{
		Time::$_currentTime = time();
	}
	
	public static function setThenGetSystemTime()
	{
		Time::setToSystemTime();
		return Time::getCurrentTime();
	}
}

?>