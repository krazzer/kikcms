<?php

namespace KikCMS\Classes;


use Helpers\TestHelper;
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

        $this->assertEquals(['test' => 'test'], $translator->getUserTranslations());
    }
}
