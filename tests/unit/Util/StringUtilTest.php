<?php

namespace unit\Util;


use KikCMS\Services\Util\StringService;
use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    public function testDashesToCamelCase()
    {
        $stringService = new StringService();

        $this->assertEquals('camelCase', $stringService->dashesToCamelCase('---camel-case----'));
        $this->assertEquals('CamelCase', $stringService->dashesToCamelCase('camel-case', true));
    }

    public function testUnderscoresToCamelCase()
    {
        $stringService = new StringService();

        $this->assertEquals('camelCase', $stringService->underscoresToCamelCase('___camel_case____'));
        $this->assertEquals('CamelCase', $stringService->underscoresToCamelCase('camel_case', true));
    }
}
