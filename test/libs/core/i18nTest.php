<?php

namespace phpsec;

require_once __DIR__ . "/../../../libs/core/i18n/i18n.php";

class i18nTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $i18n = new i18n();
    }

    public function testHelloWorld()
    {
        $this->assertSame('Hello World', \L::helloworld);
    }

    public function test404()
    {
        $this->assertSame('Not Found', \L::error_404);
    }
}