<?php
declare(strict_types=1);

use KikCMS\Classes\Monolog\PhalconHtmlFormatter;

class PhalconHtmlFormatterTest extends \Codeception\Test\Unit
{
    public function testRemoveConfig()
    {
        $formatter = new PhalconHtmlFormatter();

        $result = $formatter->removeConfig([]);

        $this->assertEquals($result, []);

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

class PhalconHtmlFormatterTestTestClass extends \Phalcon\Di\Injectable
{

}