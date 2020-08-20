<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\Unit;
use KikCMS\Classes\WebForm\Fields\TextField;
use KikCMS\Forms\UrlToId;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\UrlService;

class UrlToIdTest extends Unit
{
    public function testToStorage()
    {
        $urlToId = new UrlToId(new TextField('key', 'label'), 'en');

        $pageLanguage = $this->createMock(PageLanguage::class);
        $pageLanguage->method('getPageId')->willReturn(1);

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('getPageLanguageByUrlPath')->willReturn($pageLanguage);

        $urlToId->urlService = $urlService;

        $this->assertEquals(1, $urlToId->toStorage('x'));

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('getPageLanguageByUrlPath')->willReturn(null);

        $urlToId->urlService = $urlService;

        $this->assertEquals('x', $urlToId->toStorage('x'));
    }

    public function testToDisplay()
    {
        $urlToId = new UrlToId(new TextField('key', 'label'), 'en');

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('getUrlByPageId')->willReturn('url');

        $urlToId->urlService = $urlService;

        $this->assertEquals('x', $urlToId->toDisplay('x'));
        $this->assertEquals('url', $urlToId->toDisplay(1));
    }
}
