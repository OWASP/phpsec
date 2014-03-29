<?php
namespace phpsec;



/**
 * Required Files
 */
require_once __DIR__ . "/../../../libs/crypto/confidentialstring.php";



class ConfidentialStringTest extends \PHPUnit_Framework_TestCase
{



	/**
	 * Function to test the functionality of confidentialString()
	 */
	public function testConfidentialStringIsBasicallyWorking()
	{
		$this->assertSame("root", confidentialString(':sENSt7jtm5WBRy14P95atM8qa8ttFt0COQwkvyIKca8='));
		$this->assertSame("testing", confidentialString(':G7vkJRN2l0XEoPYpwxG6vAMJczDojOz2vMrVe9SW7Vo='));
		$this->assertSame("0123456789012345678901234567890123456789", confidentialString(':70wEpmmYmIgYRRd3cPQj2CHmPIjoa8t+YdUkn02705ZmPK3o1+Yp627sKxOWTYO7yREZgUWT0zecT1oYjxA09w=='));
	}



	/**
	 * Function to test if this function works properly even if zero bytes are present
	 */
	public function testConfidentialStringDoesNotHarmIntentionalZerobytes()
	{
		$this->assertSame("\0root", confidentialString(':1iixApMMhEWBAJbwuDwuYzlC0ienWPGtrBzRwnKORHM='));
		$this->assertSame("root\0", confidentialString(':qa+VdvIdz+yzWhoroq2gC+NFZOz4b06JctQOp+dzZFg='));
		$this->assertSame("\0root\0", confidentialString(':YWSVyb+oX78SOtkav7dYCcRzJ0tzyj2xfXKUaiQSPSU='));
	}

}
