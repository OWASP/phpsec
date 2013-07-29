<?php

namespace phpsec;

require_once(__DIR__ . '/../../../libs/http/http.php');

class HttpRequest_Test extends \PHPUnit_Framework_TestCase
{
	public function testIpAddress()
	{
		$this->assertTrue((bool)filter_var(HttpRequest::IP(), FILTER_VALIDATE_IP), 'Function does not return IP address.');
	}

	public function testPortReadable()
	{
		// The CLI has no port, the return value should be NULL then.
		$this->assertNull(HttpRequest::PortReadable());
	}
}
