<?php
namespace phpsec;

class Rand
{
	/**
	 * For now I have just use jframework's random function. I will return to this at the end.
	 * @return type
	 */
	private static $randomSeed=null;
	
	/**
	 * Provides a random 32 bit number
	 * if openssl is available, it is cryptographically secure. Otherwise all available entropy is gathered.
	 * @return number
	 */
	private static function Random()
	{
		if (function_exists("openssl_random_pseudo_bytes"))
			$random32bit=(int)(hexdec(bin2hex(openssl_random_pseudo_bytes(4))));
		else
		{
			$random64bit="";
			if (self::$randomSeed===null)
			{
				$entropy=1;
				if (function_exists("posix_getpid"))
					$entropy*=posix_getpid();
				if (function_exists("memory_get_usage"))
					$entropy*=memory_get_usage();
				list ($usec, $sec)=explode(" ",microtime());
				$usec*=1000000;
				$entropy*=$usec;
				self::$randomSeed=$entropy;
				mt_srand(self::$randomSeed);
			}
			$random32bit=mt_rand();
		}
		return $random32bit;
	}
	
	public static function randLen($min=0,$max=null)
	{
		if ($max===null)
			$max=1<<31;
		
		if ($min < 0)
			return Rand::Random()%($max-$min)+$min;
		else
			return abs(Rand::Random()%($max-$min)+$min);
	}
	
	public static function generateRandom($Length=32)
	{
		return substr(hash("sha512",  Rand::randLen()),0,$Length);
	}
}

?>