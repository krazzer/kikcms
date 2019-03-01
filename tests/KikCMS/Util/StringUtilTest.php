<?php

namespace KikCMS\Util;


use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    public function testDashesToCamelCase()
    {
        $this->assertEquals('camelCase', StringUtil::dashesToCamelCase('---camel-case----'));
        $this->assertEquals('CamelCase', StringUtil::dashesToCamelCase('camel-case', true));
    }

    public function testUnderscoresToCamelCase()
    {
        $this->assertEquals('camelCase', StringUtil::underscoresToCamelCase('___camel_case____'));
        $this->assertEquals('CamelCase', StringUtil::underscoresToCamelCase('camel_case', true));
    }
}
