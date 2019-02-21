<?php

namespace KikCMS\Classes;


use Helpers\TestHelper;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testFlatten()
    {
        $translator = (new TestHelper)->getTranslator();

        $result = $translator->flatten(['key' => ['subkey' => ['subsubkey' => 'value']]]);

        $this->assertEquals(['key.subkey.subsubkey' => 'value'], $result);
    }
}
