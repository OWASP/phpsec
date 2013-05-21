<?php
namespace phpsec;

$presentDirectory = getcwd();
chdir(  dirname(__FILE__) );

require_once ('Exception.class.php');

chdir($presentDirectory);

class Time
{
	public static $realTime = true;
	private static $_currentTime = 0;
	
	public static function time()
	{
		if(Time::$realTime == true)
		{
			Time::resetTime ();
		}
		else
		{
			Time::$_currentTime += 10;	//If you have to fake time, then for each call, increase 'n' seconds from the fake time.
		}
		
		return Time::$_currentTime;
	}
	
	public static function setTime($time)
	{
		if( gettype( $time != "integer" ) || strlen( (string)$time ) != 10 )
			throw new IntegerNotFoundException("<BR>ERROR: Integer is required to generate UNIX Timestamp. " . gettype($time) . " was found.<BR>");
		
		Time::$_currentTime = $time;
	}
	
	public static function resetTime()
	{
		Time::$_currentTime = \time();
	}
	
	public static function moveTime($difference)
	{
		if( gettype( $difference != "integer" ) )
			throw new IntegerNotFoundException("<BR>ERROR: Integer is required to generate UNIX Timestamp. " . gettype($difference) . " was found.<BR>");
		
		Time::$_currentTime = (int)\time() - (int)$difference;
	}
}

?>