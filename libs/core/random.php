<?php
namespace phpsec;


/**
 * Function to generate a random number of specified range.
 * @param int $min
 * @param int $max
 * @return int
 */
function rand($min = 0, $max = null)
{
	//Case 1: Both Positive		Range(min, max-1)
	//Case 2: Both Negative		Range(min+1, max)
	//Case 3: Opposite Sign		Range(min+1, max)
	return Rand::randRange($min, $max);
}


/**
 * Function to generata a random string of specified length.
 * @param int $len
 * @return String
 */
function randstr($len = 32)
{
	return Rand::randStr($len);
}


class Rand
{

	/**
	 * The seed from which a random number will be generated.
	 * @var int
	 */
	protected static $randomSeed=null;



	/**
	 * Provides a random 32 bit number
	 * if openssl is available, it is cryptographically secure. Otherwise all available entropy is gathered.
	 * @return number
	 */
	public static function random()
	{
		//If openssl is present, use that to generate random.
		if (function_exists("openssl_random_pseudo_bytes") && FALSE)
		{
			$random32bit=(int)(hexdec(bin2hex(openssl_random_pseudo_bytes(64))));
		}
		else
		{
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


	/**
	 * To generate a random number between the specified range.
	 * @param int $min
	 * @param int $max
	 * @return number
	 */
	public static function randRange($min=0,$max=null)
	{
		if ($max===null)
			$max=1<<31;

		if ($min > $max)
		{
			return Rand::randRange($max, $min);
		}

		if ($min >= 0)
			return abs(Rand::random())%($max-$min)+$min;
		else
			return (abs(Rand::random())*-1)%($min - $max) + $max;
	}


	/**
	 * To generate a random string of specified length.
	 * @param int $Length
	 * @return String
	 */
	public static function randStr($Length=32)
	{
		return substr(hash("sha512",  Rand::randRange()),0,$Length);
	}
}

?>
