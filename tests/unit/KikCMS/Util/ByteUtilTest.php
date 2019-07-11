<?php

namespace KikCMS\Util;


use PHPUnit\Framework\TestCase;

class ByteUtilTest extends TestCase
{
    public function testStringToBytes()
    {
        $this->assertEquals(1024 * 1024, ByteUtil::stringToBytes('1M'));
        $this->assertEquals((1024 * 1024 * 1024 * 1024) * 3.5, ByteUtil::stringToBytes('3.5T'));
        $this->assertEquals(100, ByteUtil::stringToBytes('100'));
        $this->assertEquals(0, ByteUtil::stringToBytes('gibberish'));
        $this->assertEquals(1000, ByteUtil::stringToBytes('1000HHHHHHHb'));
    }

    public function testBytesToString()
    {
        $this->assertEquals('10GB', ByteUtil::bytesToString(pow(1024, 3) * 10));
        $this->assertEquals('10PB', ByteUtil::bytesToString(pow(1024, 5) * 10));
        $this->assertEquals('128B', ByteUtil::bytesToString(128));
    }
}
