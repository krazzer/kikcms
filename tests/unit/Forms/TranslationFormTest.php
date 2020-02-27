<?php
declare(strict_types=1);

namespace Forms;

use Helpers\Unit;
use KikCMS\Forms\TranslationForm;
use KikCMS\Models\TranslationKey;

class TranslationFormTest extends Unit
{
    public function testGetModel()
    {
        $translationForm = new TranslationForm();

        $this->assertEquals(TranslationKey::class, $translationForm->getModel());
    }

    public function testInitializeAndSave()
    {
        $translationForm = new TranslationForm();
        $translationForm->setDI($this->getDbDi());

        $this->addDefaultLanguage();

        $translationForm->initializeForm();

        $this->assertGreaterThan(1, $translationForm->getFieldMap()->count());

        $translationForm->successAction(['key' => 1]);
    }
}
