<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\Unit;
use KikCMS\Forms\LanguageForm;
use KikCMS\Models\Language;
use Phalcon\Cache\Adapter\AdapterInterface;
use ReflectionMethod;

class LanguageFormTest extends Unit
{
    public function testGetModel()
    {
        $languageForm = new LanguageForm();

        $this->assertEquals(Language::class, $languageForm->getModel());
    }

    public function testOnSave()
    {
        $method = new ReflectionMethod(LanguageForm::class, 'onSave');
        $method->setAccessible(true);

        $languageForm = new LanguageForm();

        $cacheMock = $this->createMock(AdapterInterface::class);
        $cacheMock->expects($this->once())->method('delete');

        $languageForm->cache = $cacheMock;

        $method->invoke($languageForm);
    }
}
