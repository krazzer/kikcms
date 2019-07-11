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
}
