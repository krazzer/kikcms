<?php

namespace unit\Services\Website;


use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\UserService;
use KikCMS\Services\Website\FrontendService;
use PHPUnit\Framework\MockObject\MockObject;

class FrontendServiceTest extends Unit
{
    public function testGetLangSwitchVariables()
    {
        $frontendService = new FrontendService();

        $frontendService->setDI($this->getDbDi());

        $page  = $this->createPage(1, null);
        $page2 = $this->createPage(2, 'default');

        $pageLanguage = $this->createPageLanguage($page, 1, 'page-en', 'en');
        $pageLanguage->save();

        $this->createPageLanguage($page2, 1, 'home-en', 'en')->save();
        $this->createPageLanguage($page2, 1, 'home-nl', 'nl')->save();

        // there are no languages configured, so return nothing
        $this->assertEquals(['langUrlMap' => []], $frontendService->getLangSwitchVariables($pageLanguage));

        $this->addLanguage('en');
        $this->addLanguage('nl');

        $frontendService->cache->delete('languages');
        $frontendService->cache->delete('url:1:en:otherLangMap');

        $expected = [
            'langUrlMap'    => ['en' => '/page-en', 'nl' => '/home-nl'],
            'otherLangCode' => 'nl',
            'otherLangUrl'  => '/home-nl',
        ];

        $this->assertEquals($expected, $frontendService->getLangSwitchVariables($pageLanguage));

        $this->createPageLanguage($page, 1, 'page-nl', 'nl')->save();

        $frontendService->cache->delete('url:1:en:otherLangMap');

        $expected = [
            'langUrlMap'    => ['en' => '/page-en', 'nl' => '/page-nl'],
            'otherLangCode' => 'nl',
            'otherLangUrl'  => '/page-nl',
        ];

        $this->assertEquals($expected, $frontendService->getLangSwitchVariables($pageLanguage));
    }

    public function testGetPageLanguageToLoadByUrlPath()
    {
        $frontendService = new FrontendService();

        $frontendService->setDI((new TestHelper)->getTestDi());
        $frontendService->userService = $this->createUserServiceMock(false);

        // default page
        $pageLanguage                         = $this->createPageLanguage(new Page);
        $frontendService->pageLanguageService = $this->createPageLanguageServiceMock($pageLanguage, 2);
        $this->assertEquals($pageLanguage, $frontendService->getPageLanguageToLoadByUrlPath('/'));
        $this->assertEquals($pageLanguage, $frontendService->getPageLanguageToLoadByUrlPath(''));

        // pageLanguage missing
        $frontendService->urlService = $this->createUrlServiceMock(null);
        $this->assertNull($frontendService->getPageLanguageToLoadByUrlPath('/some-url'));

        // page missing
        $frontendService->urlService = $this->createUrlServiceMock(new PageLanguage);
        $this->assertNull($frontendService->getPageLanguageToLoadByUrlPath('/some-url'));

        // active and not logged in
        $pageLanguage                = $this->createPageLanguage(new Page);
        $frontendService->urlService = $this->createUrlServiceMock($pageLanguage);
        $this->assertEquals($pageLanguage, $frontendService->getPageLanguageToLoadByUrlPath('/some-url'));

        // not active and not logged in
        $frontendService->urlService = $this->createUrlServiceMock($this->createPageLanguage(new Page, 0));
        $this->assertNull($frontendService->getPageLanguageToLoadByUrlPath('/some-url'));

        // active and logged in
        $pageLanguage                 = $this->createPageLanguage(new Page);
        $frontendService->userService = $this->createUserServiceMock(true);
        $frontendService->urlService  = $this->createUrlServiceMock($pageLanguage);
        $this->assertEquals($pageLanguage, $frontendService->getPageLanguageToLoadByUrlPath('/some-url'));

        // not active and logged in
        $pageLanguage                = $this->createPageLanguage(new Page, 0);
        $frontendService->urlService = $this->createUrlServiceMock($pageLanguage);
        $this->assertEquals($pageLanguage, $frontendService->getPageLanguageToLoadByUrlPath('/some-url'));
    }

    /**
     * @param null $page
     * @param int $active
     * @param string|null $name
     * @param null $langCode
     * @return PageLanguage
     */
    private function createPageLanguage($page = null, $active = 1, $name = null, $langCode = null): PageLanguage
    {
        $pageLanguage = new PageLanguage();

        $pageLanguage->active  = $active;
        $pageLanguage->page    = $page;
        $pageLanguage->page_id = $page->id;

        if ($name) {
            $pageLanguage->setName($name)->setSlug($name);
        }

        if ($langCode) {
            $pageLanguage->language_code = $langCode;
        }

        return $pageLanguage;
    }

    /**
     * @param PageLanguage|null $willReturn
     * @return MockObject
     */
    private function createUrlServiceMock(?PageLanguage $willReturn): MockObject
    {
        $urlService = $this->createMock(UrlService::class);
        $urlService->method('getPageLanguageByUrlPath')->willReturn($willReturn);
        $urlService->expects($this->once())->method('getPageLanguageByUrlPath');

        return $urlService;
    }

    /**
     * @param bool $willReturn
     * @return MockObject
     */
    private function createUserServiceMock(bool $willReturn): MockObject
    {
        $userService = $this->createMock(UserService::class);
        $userService->method('isLoggedIn')->willReturn($willReturn);

        return $userService;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @param int $timesCalled
     * @return PageLanguageService|MockObject
     */
    private function createPageLanguageServiceMock(PageLanguage $pageLanguage, int $timesCalled)
    {
        $pageLanguageService = $this->createMock(PageLanguageService::class);
        $pageLanguageService->method('getDefault')->willReturn($pageLanguage);
        $pageLanguageService->expects($this->exactly($timesCalled))->method('getDefault');

        return $pageLanguageService;
    }

    /**
     * @param int $id
     * @param string|null $key
     * @return Page
     */
    private function createPage(int $id, string $key = null): Page
    {
        $page       = new Page;
        $page->id   = $id;
        $page->type = Page::TYPE_PAGE;
        $page->key  = $key;
        $page->lft  = $id * 2 - 1;
        $page->rgt  = $id * 2;
        $page->save();

        return $page;
    }
}
