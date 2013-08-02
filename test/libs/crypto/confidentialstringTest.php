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
		$this->assertSame(str_pad("root", 32, "\0", STR_PAD_RIGHT), confidentialString(':/X6NSUlAagxmmLNWRZBA8fyJbmQZmAB7VcgzHHfTxwA='));
		$this->assertSame(str_pad("testing", 32, "\0", STR_PAD_RIGHT), confidentialString(':bpsY8XdMOZdO32Jnoh7wqh1Og3ogQkIs3e6k8Kvk1J0='));
		$this->assertSame(str_pad("0123456789012345678901234567890123456789", 64, "\0", STR_PAD_RIGHT), confidentialString(':7ihOuK5EdTV0+SZYXQp/jPcsxZ0E2xyT86Mf8ykrgy2vtoIGFmKc4EmAqzrXzw9ZcYjUecSyzWLbL5zIGm80cQ=='));
	}
}
