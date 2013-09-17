<?php

namespace phpsec;
require_once __DIR__ . "/../../../libs/core/random.php";


class RandTest extends \PHPUnit_Framework_TestCase {


    public function testRandomStringLengthShouldEqualRequestedLength()
    {
        $this->assertSame(32, strlen(Rand::randStr(32)));
        $this->assertSame(64, strlen(Rand::randStr(64)));
        $this->assertSame(1024, strlen(Rand::randStr(1024)));
    }
}
