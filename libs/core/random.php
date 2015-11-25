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
    //Case 1: Both Positive     Range(min, max-1)
    //Case 2: Both Negative     Range(min+1, max)
    //Case 3: Opposite Sign     Range(min+1, max)
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

final class Rand
{
    /**
     * Provides a random 32 bit number
     *
     * @return number
     */
    public static function random()
    {
        return self::randRange();
    }

    /**
     * To generate a random number between the specified range.
     * @param int $min
     * @param int $max
     * @return number
     */
    public static function randRange($min = 0, $max = null)
    {
        if (null === $max) {
            $max = PHP_INT_MAX;
        }

        return random_int($min, $max);
    }

    /**
     * To generate a random string of specified length.
     * @param int $length
     * @return String
     */
    public static function randStr($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}
