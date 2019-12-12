<?php
declare(strict_types=1);

namespace unit\Services\Pages;


use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\UrlService;

class UrlServiceTest extends Unit
{
    public function testDeduplicateUrl()
    {
        $urlService = new UrlService();
        $urlService->setDI($this->getDbDi());

        $pageLanguage = $this->createPageLanguage('test');
        $pageLanguage->save();

        // exists, must change slug
        $pageLanguage = $this->createPageLanguage('test');
        $urlService->deduplicateUrl($pageLanguage);
        $this->assertEquals('test-1', $pageLanguage->getSlug());

        // doesnt exists, slug stays the same
        $pageLanguage = $this->createPageLanguage('other-test');
        $urlService->deduplicateUrl($pageLanguage);
        $this->assertEquals('other-test', $pageLanguage->getSlug());
    }

    /**
     * @param string $slug
     * @return PageLanguage
     */
    public function createPageLanguage(string $slug): PageLanguage
    {
        $page = new Page();

        $page->type = Page::TYPE_PAGE;
        $page->key  = null;
        $page->lft  = null;
        $page->rgt  = null;

        $pageLanguage = new PageLanguage();
        $pageLanguage->setSlug($slug);
        $pageLanguage->page = $page;
        $pageLanguage->language_code = 'en';

        return $pageLanguage;
    }
}