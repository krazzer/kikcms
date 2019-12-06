<?php

namespace Services\Pages;

use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;

class PageLanguageServiceTest extends Unit
{
    public function testCheckAndUpdateSlug()
    {
        $pageLanguageService = new PageLanguageService();
        $pageLanguageService->setDI($this->getDbDi());

        $pageLanguage = new PageLanguage();
        $pageLanguage->setSlug('slug');

        // has slug, so does nothing
        $result = $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertNull($result);

        // page is menu, so do nothing
        $page = new Page();
        $page->type = Page::TYPE_MENU;

        $pageLanguage = new PageLanguage();
        $pageLanguage->page = $page;

        $result = $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertNull($result);

        // page is link, so do nothing
        $page = new Page();
        $page->type = Page::TYPE_LINK;

        $pageLanguage = new PageLanguage();
        $pageLanguage->page = $page;

        $result = $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertNull($result);

        // no slug, needs to be updated
        $page = new Page();
        $page->type = Page::TYPE_PAGE;

        $pageLanguage = new PageLanguage();
        $pageLanguage->page = $page;
        $pageLanguage->name = 'test';
        $pageLanguage->language_code = 'en';

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('toSlug')->willReturn($pageLanguage->getName());

        $pageLanguageService->urlService = $urlService;

        $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertEquals('test', $pageLanguage->getSlug());

        // url path exists
        $page = new Page();
        $page->type = Page::TYPE_PAGE;

        $parentPageLanguage = new PageLanguage();
        $parentPageLanguage->setSlug('parent');

        $pageLanguage = $this->createMock(PageLanguage::class);
        $pageLanguage->method('getParentWithSlug')->willReturn($parentPageLanguage);
        $pageLanguage->initialize();
        $pageLanguage->page = $page;
//        $pageLanguage->page = $page;

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('toSlug')->willReturn($pageLanguage->getName());
        $urlService->method('urlPathExists')->willReturn(true);

        $urlService->expects($this->once())->method('deduplicateUrl');

        $pageLanguageService->urlService = $urlService;
        $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertEquals('test', $pageLanguage->getSlug());
    }
}
