<?php
namespace phpsec;
require_once realpath(__DIR__."/../../libs/core/error.php");

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
	function testTrue()
	{
		$this->assertTrue(true);
	}

}