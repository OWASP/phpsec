<?php 

	namespace phpsec;

	require_once ( __DIR__ . '/../../../libs/http/Http.class.php' );

	class HttpRequest_Test extends \PHPUnit_Framework_TestCase
	{
		public function testIpAddress()
		{
			$this->assertTrue( (bool)filter_var(HttpRequest::IP(),FILTER_VALIDATE_IP), 'Function does not return IP address.' );
		}
		
		public function testHttpRefererException()
		{
			try {
				$hr = HttpRequest::getParameter('HTTP_REFERER');
			}
			catch (\phpsec\HttpRequestInsecureParameterException $expected) {
				return;
			}
			$this->fail ('Expected HttpRequestInsecureParameterException not thrown.');
		}
	}

?>