<?php

namespace KikCMS\Util;


use KikCMS\Services\Util\ByteService;
use PHPUnit\Framework\TestCase;

class ByteUtilTest extends TestCase
{
    public function testStringToBytes()
    {
        $byteService = new ByteService();

        $this->assertEquals(1024 * 1024, $byteService->stringToBytes('1M'));
        $this->assertEquals((1024 * 1024 * 1024 * 1024) * 3.5, $byteService->stringToBytes('3.5T'));
        $this->assertEquals(100, $byteService->stringToBytes('100'));
        $this->assertEquals(0, $byteService->stringToBytes('gibberish'));
        $this->assertEquals(1000, $byteService->stringToBytes('1000HHHHHHHb'));
    }

    public function testBytesToString()
    {
        $byteService = new ByteService();

        $this->assertEquals('10GB', $byteService->bytesToString(pow(1024, 3) * 10));
        $this->assertEquals('10PB', $byteService->bytesToString(pow(1024, 5) * 10));
        $this->assertEquals('128B', $byteService->bytesToString(128));
    }
}
