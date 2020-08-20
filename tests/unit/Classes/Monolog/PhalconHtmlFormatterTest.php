<?php
declare(strict_types=1);

namespace unit\Classes\Monolog;

use Codeception\Test\Unit;
use Helpers\PhalconHtmlFormatterTestTestClass;
use KikCMS\Classes\Monolog\PhalconHtmlFormatter;

class PhalconHtmlFormatterTest extends Unit
{
    public function testRemoveConfig()
    {
        $formatter = new PhalconHtmlFormatter();

        $result = $formatter->removeConfig([]);

        $this->assertEquals([], $result);

        $object = new PhalconHtmlFormatterTestTestClass();

        $object->config = 'configValue';
        $object->key    = 'value';

        $objectResult      = new PhalconHtmlFormatterTestTestClass();
        $objectResult->key = 'value';

        $extra = new PhalconHtmlFormatterTestTestClass();
        $extra->key = ['key' => 'value'];

        $config   = ['level1' => ['level2' => $object], $extra];
        $expected = ['level1' => ['level2' => $objectResult], $extra];

        $result = $formatter->removeConfig($config);

        $this->assertEquals($result, $expected);
    }
}