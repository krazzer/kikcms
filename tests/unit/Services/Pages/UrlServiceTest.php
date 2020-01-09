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

    public function testGetUrlForLinkedPage()
    {
        $urlService = new UrlService();
        $urlService->setDI($this->getDbDi());

        $pageLanguage = new PageLanguage();

        $page = new Page();
        $page->link = null;

        $pageLanguage->language_code = 'en';
        $pageLanguage->page = $page;

        $urlService->dbService->insert(Page::class, ['id' => 1, 'type' => 'link']);
        $urlService->dbService->insert(PageLanguage::class, ['page_id' => 1, 'language_code' => 'en']);

        //no link
        $this->assertEquals('', $urlService->getUrlForLinkedPage($pageLanguage));

        //textual link with /
        $pageLanguage->page->link = '/somelink';
        $this->assertEquals('/somelink', $urlService->getUrlForLinkedPage($pageLanguage));

        //textual link without /
        $pageLanguage->page->link = 'somelink';
        $this->assertEquals('/somelink', $urlService->getUrlForLinkedPage($pageLanguage));

        //links to a link
        $pageLanguage->page->link = 1;
        $this->assertEquals('', $urlService->getUrlForLinkedPage($pageLanguage));

        //links to a link
        $page = Page::getById(1);
        $page->type = 'page';
        $page->save();

        $this->assertEquals('/', $urlService->getUrlForLinkedPage($pageLanguage));
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