<?php

class Rand
{
	/**
	 * For now I have just use PHP's random function. I will return to this at the end.
	 * @return type
	 */
	public static function generateRandom()
	{
		$rand = rand();
		
		if ($rand == null)
		{
			throw new Exception("Unable to generate a random number.");
		}
		
		return $rand;
	}
}

?>