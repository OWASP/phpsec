<?php 

namespace phpsec;

require_once ( __DIR__ . '/../../../libs/http/http.php' );

class HttpRequest_Test extends \PHPUnit_Framework_TestCase
{
    public function testIpAddress()
    {
        $this->assertTrue( (bool)filter_var(HttpRequest::IP(),FILTER_VALIDATE_IP), 'Function does not return IP address.' );
    }

    public function testPortReadable()
    {
        // The CLI has no port, but the current code adds a colon to NULL in this case. This is debatable.
        $this->assertEquals(":", HttpRequest::PortReadable());
    }
}
