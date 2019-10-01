<?php

namespace KikCMS\Services\Util;


use PHPUnit\Framework\TestCase;

class StringServiceTest extends TestCase
{
    public function testTruncate()
    {
        $stringService = new StringService();

        $this->assertEquals('bla...', $stringService->truncate('bla bla bla', 3));
        $this->assertEquals('bla bla bla', $stringService->truncate('bla bla bla'));
        $this->assertEquals('😀😁😂🤣😃...', $stringService->truncate('😀😁😂🤣😃😀😁😂🤣😃', 5));
    }

    public function testNumberToBaseString()
    {
        $stringService = new StringService();

        $this->assertEquals('100', $stringService->floatToBaseString(3844.0));
        $this->assertEquals('1xrhRyZ', $stringService->floatToBaseString(87435837589.0));
        $this->assertEquals('uk6tg7MrApvc4', $stringService->floatToBaseString(97834165978163459876537.0));
        $this->assertEquals('1', $stringService->floatToBaseString(1.0));
        $this->assertEquals('5', $stringService->floatToBaseString(5.0));
        $this->assertEquals('1C', $stringService->floatToBaseString(100.0));
        $this->assertEquals('0', $stringService->floatToBaseString(0.0));
    }
}
