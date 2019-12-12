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

    public function testGetUrlPathByPageKey()
    {
        $urlService = new UrlService();
        $urlService->setDI($this->getDbDi());

        // page doesnt exist, no it creates a dummy url
        $this->assertEquals('/page/en/default', $urlService->getUrlPathByPageKey('default'));

        $pageLanguage = $this->createPageLanguage('some-slug', 'some-key');
        $pageLanguage->save();

        // page exist, so get actual url
        $this->assertEquals('/some-slug', $urlService->getUrlPathByPageKey('some-key'));
    }

    public function testCreateUrlPathByPageLanguage()
    {
        $urlService = new UrlService();
        $urlService->setDI($this->getDbDi());

        // default page
        $pageLanguage = $this->createPageLanguage('slug', 'default');
        $this->assertEquals('/', $urlService->createUrlPathByPageLanguage($pageLanguage));

        // linked page
        $pageLanguage = $this->createPageLanguage('slug', null, Page::TYPE_LINK);
        $this->assertEquals('', $urlService->createUrlPathByPageLanguage($pageLanguage));

        // saved page
        $pageLanguage = $this->createPageLanguage('slug');
        $pageLanguage->save();

        $this->assertEquals('/slug', $urlService->createUrlPathByPageLanguage($pageLanguage));
    }

    /**
     * @param string $slug
     * @param string|null $key
     * @param string $type
     * @return PageLanguage
     */
    private function createPageLanguage(string $slug, string $key = null, string $type = Page::TYPE_PAGE): PageLanguage
    {
        $page = new Page();

        $page->type = $type;
        $page->key  = $key;
        $page->lft  = null;
        $page->rgt  = null;
        $page->link = null;

        $pageLanguage = new PageLanguage();
        $pageLanguage->setSlug($slug);
        $pageLanguage->page          = $page;
        $pageLanguage->language_code = 'en';

        return $pageLanguage;
    }
}