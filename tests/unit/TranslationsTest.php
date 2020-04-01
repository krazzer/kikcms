<?php
declare(strict_types=1);

use Codeception\Test\Unit;
use KikCMS\Classes\Translator;
use KikCMS\DataTables\Translations;
use KikCMS\Forms\TranslationForm;
use KikCMS\Services\CacheService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\ModelService;
use KikCMS\Services\Util\StringService;

class TranslationsTest extends Unit
{
    public function testDelete()
    {
        $translations = new Translations();

        $modelService = $this->createMock(ModelService::class);
        $modelService->method('getObjects')->willReturn([]);

        $cacheService = $this->createMock(CacheService::class);
        $cacheService->expects($this->once())->method('clear');

        $translations->modelService = $modelService;
        $translations->cacheService = $cacheService;

        $translations->delete([]);
    }

    public function testGetFormClass()
    {
        $translations = new Translations();

        $this->assertEquals(TranslationForm::class, $translations->getFormClass());
    }

    public function testInitialize()
    {
        $translations = new Translations();

        $languageService = $this->createMock(LanguageService::class);
        $languageService->method('getDefaultLanguageCode')->willReturn('en');

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('x');

        $translations->translator      = $translator;
        $translations->languageService = $languageService;
        $translations->stringService   = new StringService;

        $method = new ReflectionMethod(Translations::class, 'initialize');
        $method->setAccessible(true);

        $method->invoke($translations);

        $this->assertEquals('x', $translations->formatValue('value', null, ['key' => 'key']));
    }
}
