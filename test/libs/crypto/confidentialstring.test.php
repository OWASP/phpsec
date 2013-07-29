<?php
namespace phpsec;

require_once __DIR__ . "/../../../libs/crypto/confidentialstring.php";

class ConfidentialStringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Function to test the functionality of confidentialString()
	 */
	public function testConfidentialString()
	{
		$this->assertSame("root                            ", confidentialString(':/X6NSUlAagxmmLNWRZBA8fyJbmQZmAB7VcgzHHfTxwA='));
		$this->assertSame("testing                         ", confidentialString(':bpsY8XdMOZdO32Jnoh7wqh1Og3ogQkIs3e6k8Kvk1J0='));
	}
}
