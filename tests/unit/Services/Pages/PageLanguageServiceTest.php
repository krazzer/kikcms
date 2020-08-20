<?php

namespace unit\Services\Pages;

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

        // has slug, so does nothing
        $pageLanguage = $this->createPageLanguage('slug');
        $result = $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertNull($result);

        // page is menu, so do nothing
        $pageLanguage = $this->createPageLanguage(null, Page::TYPE_MENU);
        $result = $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertNull($result);

        // page is link, so do nothing
        $pageLanguage = $this->createPageLanguage(null, Page::TYPE_LINK);
        $result = $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertNull($result);

        // no slug, needs to be updated
        $pageLanguage = $this->createPageLanguage();

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('toSlug')->willReturn($pageLanguage->getName());

        $pageLanguageService->urlService = $urlService;

        $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertEquals('test', $pageLanguage->getSlug());

        // url path exists
        $parentPageLanguage = new PageLanguage();
        $parentPageLanguage->setSlug('parent');

        $page = $this->createMock(Page::class);
        $page->type = Page::TYPE_PAGE;
        $page->method('getParentPageLanguageWithSlugByLangCode')->willReturn($parentPageLanguage);

        $pageLanguage = $this->createPageLanguage();
        $pageLanguage->page = $page;

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('toSlug')->willReturn($pageLanguage->getName());
        $urlService->method('urlPathExists')->willReturn(true);

        $urlService->expects($this->once())->method('deduplicateUrl');

        $pageLanguageService->urlService = $urlService;
        $pageLanguageService->checkAndUpdateSlug($pageLanguage);

        $this->assertEquals('test', $pageLanguage->getSlug());
    }

    public function testCreateForAlias()
    {
        $pageLanguageService = new PageLanguageService();
        $pageLanguageService->setDI($this->getDbDi());

        // no alias
        $page = new Page();
        $page->alias = null;

        $pageLanguageService->createForAlias($page);

        // no alias
        $aliasPage = new Page();
        $aliasPage->id = 1;
        $aliasPage->save();

        $aliasPageLanguage = new PageLanguage();
        $aliasPageLanguage->page_id = 1;
        $aliasPageLanguage->language_code = 'en';
        $aliasPageLanguage->name = 'test';
        $aliasPageLanguage->save();

        $page->alias = 1;
        $page->id = 2;
        $page->save();

        $pageLanguageService->createForAlias($page);

        $pageLanguages = PageLanguage::find(['page_id = 2']);

        $this->assertCount(1, $pageLanguages);
    }

    /**
     * @param string|null $slug
     * @param string $pageType
     * @return PageLanguage
     */
    private function createPageLanguage(string $slug = null, string $pageType = Page::TYPE_PAGE): PageLanguage
    {
        $page = new Page();
        $page->type = $pageType;

        $pageLanguage = new PageLanguage();
        $pageLanguage->page = $page;
        $pageLanguage->setSlug($slug);
        $pageLanguage->name = 'test';
        $pageLanguage->language_code = 'en';

        return $pageLanguage;
    }
}
