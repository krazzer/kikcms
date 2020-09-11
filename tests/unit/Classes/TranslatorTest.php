<?php

namespace unit\Classes;


use Helpers\TestHelper;
use KikCMS\Classes\Translator;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testFlatten()
    {
        $translator = (new TestHelper)->getTranslator();

        $result = $translator->flatten(['key' => ['subkey' => ['subsubkey' => 'value']]]);

        $this->assertEquals(['key.subkey.subsubkey' => 'value'], $result);
    }

    public function testGetUserTranslations()
    {
        $translator = new Translator('nl');

        $translator->setDI((new TestHelper)->getTestDi());

        $translator->db->delete(TranslationKey::TABLE);
        $translator->db->delete(TranslationValue::TABLE);

        $translator->dbService->insert(TranslationKey::class, ['id' => 1, 'key' => 'test']);
        $translator->dbService->insert(TranslationValue::class, ['key_id' => 1, 'language_code' => 'nl', 'value' => 'test']);

        $this->assertEquals(['test' => 'test'], $translator->getUserTranslations('nl'));
    }

    public function testGetCmsTranslationGroupKeys()
    {
        $di = (new TestHelper)->getTestDi();

        $translator = new Translator('nl', ['nl' => (new TestHelper)->getTestFilesPath() . 'nl.php']);
        $translator->setDI($di);

        $expected = ['test.subtest.subsubtest'];

        $this->assertEquals($expected, $translator->getCmsTranslationGroupKeys('test'));
    }
}
