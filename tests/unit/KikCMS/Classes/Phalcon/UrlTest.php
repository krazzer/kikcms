<?php

namespace KikCMS\Classes\Phalcon;


use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function testConvertArguments()
    {
        $url = new Url();

        $this->assertEquals([], $url->convertArguments(null, []));
        $this->assertEquals(['var' => 100], $url->convertArguments(100, ['var' => 1]));
        $this->assertEquals(['var1' => 100, 'var2' => 200], $url->convertArguments([100,200], ['var2' => 2, 'var1' => 1]));
        $this->assertEquals(['var1' => 100, 'var2' => 200], $url->convertArguments(['var1' => 100, 'var2' => 200], ['var2' => 2, 'var1' => 1]));
    }

    public function testFixDoubleSlashes()
    {
        $url = new Url();

        $this->assertEquals('https://test.nl', $url->fixDoubleSlashes('https://test.nl'));
        $this->assertEquals('test', $url->fixDoubleSlashes('/test'));
    }
}
