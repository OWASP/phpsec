<?php
namespace phpsec;

require_once __DIR__ . "/../../../libs/crypto/confidentialstring.php";

class ConfidentialStringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Function to test the functionality of confidentialString()
	 */
	public function testConfidentialStringIsBasicallyWorking()
	{
		$this->assertSame("root", confidentialString(':/X6NSUlAagxmmLNWRZBA8fyJbmQZmAB7VcgzHHfTxwA='));
		$this->assertSame("testing", confidentialString(':bpsY8XdMOZdO32Jnoh7wqh1Og3ogQkIs3e6k8Kvk1J0='));
		$this->assertSame("0123456789012345678901234567890123456789", confidentialString(':7ihOuK5EdTV0+SZYXQp/jPcsxZ0E2xyT86Mf8ykrgy2vtoIGFmKc4EmAqzrXzw9ZcYjUecSyzWLbL5zIGm80cQ=='));
	}

	public function testConfidentialStringDoesNotHarmIntentionalZerobytes()
	{
		$this->assertSame("\0root", confidentialString(':+25AatjlbzvpXA0RIKW3RwdIEzhiTbxtsfyOD/QQizY='));
		$this->assertSame("root\0", confidentialString(':/X6NSUlAagxmmLNWRZBA8fyJbmQZmAB7VcgzHHfTxwA='));
		$this->assertSame("\0root\0", confidentialString(':+25AatjlbzvpXA0RIKW3RwdIEzhiTbxtsfyOD/QQizY='));
	}

	public function testConfidentialStringDoesNotReplaceVariablesOrFunctionCalls()
	{
		$password = str_repeat('a', rand(2, 30));
		// the next line should be:
		// $this->assertSame($password, confidentialString($password));
		$this->assertSame($password, confidentialString(':ddSTcsjF+oR+U0We583AIqVYhCmHulnLurBOiAJCX0A='));
	}
}
