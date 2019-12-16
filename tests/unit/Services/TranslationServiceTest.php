<?php
declare(strict_types=1);

namespace Services;

use Helpers\Unit;
use KikCMS\Models\TranslationKey;
use KikCMS\Models\TranslationValue;
use KikCMS\Services\TranslationService;

class TranslationServiceTest extends Unit
{
    public function testGetTranslationValue()
    {
        $translationService = new TranslationService();
        $translationService->setDI($this->getDbDi());

        $translationKey = new TranslationKey();
        $translationKey->id = 1;

        $translationValue = new TranslationValue();
        $translationValue->key_id = 1;
        $translationValue->value = 'x';
        $translationValue->language_code = 'en';

        $translationKey->save();
        $translationValue->save();

        $this->assertEquals('x', $translationService->getTranslationValue(1, 'en'));
        $this->assertTrue($translationService->valueExists(1, 'en'));
    }

    public function testGetTranslationExists()
    {
        $translationService = new TranslationService();
        $translationService->setDI($this->getDbDi());

        $translationKey = new TranslationKey();
        $translationKey->id = 1;

        $translationValue = new TranslationValue();
        $translationValue->key_id = 1;
        $translationValue->value = 'x';
        $translationValue->language_code = 'en';

        $translationKey->save();
        $translationValue->save();

        $this->assertTrue($translationService->valueExists(1, 'en'));
    }

    public function testSaveValue()
    {
        $translationService = new TranslationService();
        $translationService->setDI($this->getDbDi());

        $this->addDefaultLanguage();

        $translationKey = new TranslationKey();
        $translationKey->id = 1;

        $translationKey->save();

        $translationService->saveValue('x', 1, 'en');

        $this->assertEquals('x', $translationKey->valueEn->value);
    }

    public function testCreateNewTranslationKeyId()
    {
        $translationService = new TranslationService();
        $translationService->setDI($this->getDbDi());

        $this->assertIsNumeric($translationService->createNewTranslationKeyId());
    }
}
