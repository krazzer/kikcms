<?php

namespace unit\Services\Website;


use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Models\Language;
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

        $page = new Page;
        $page->id = 1;
        $page->type = Page::TYPE_PAGE;
        $page->key = null;
        $page->lft = 1;
        $page->rgt = 2;
        $page->save();

        $page = new Page;
        $page->id = 2;
        $page->type = Page::TYPE_PAGE;
        $page->key = 'default';
        $page->lft = 3;
        $page->rgt = 4;
        $page->save();

        $pageLanguage = new PageLanguage;
        $pageLanguage->id = 1;
        $pageLanguage->active = 1;
        $pageLanguage->language_code = 'en';
        $pageLanguage->name = 'page-en';
        $pageLanguage->setSlug('page-en');
        $pageLanguage->page_id = 1;
        $pageLanguage->save();

        $pageLanguageNl = new PageLanguage;
        $pageLanguageNl->id = 3;
        $pageLanguageNl->active = 1;
        $pageLanguageNl->language_code = 'en';
        $pageLanguageNl->name = 'home-en';
        $pageLanguageNl->setSlug('home-en');
        $pageLanguageNl->page_id = 2;
        $pageLanguageNl->save();

        $pageLanguageNl = new PageLanguage;
        $pageLanguageNl->id = 4;
        $pageLanguageNl->active = 1;
        $pageLanguageNl->language_code = 'nl';
        $pageLanguageNl->name = 'home-nl';
        $pageLanguageNl->setSlug('home-nl');
        $pageLanguageNl->page_id = 2;
        $pageLanguageNl->save();

        // there are no languages configured, so return nothing
        $this->assertEquals(['langUrlMap' => []], $frontendService->getLangSwitchVariables($pageLanguage));

        $languageEn = new Language();

        $languageEn->code   = 'en';
        $languageEn->active = 1;
        $languageEn->save();

        $languageNl = new Language();

        $languageNl->code   = 'nl';
        $languageNl->active = 1;
        $languageNl->save();

        $frontendService->cache->delete('languages');
        $frontendService->cache->delete('url:1:en:otherLangMap');

        $expected = [
            'langUrlMap'    => ['en' => '/page-en', 'nl' => '/home-nl'],
            'otherLangCode' => 'nl',
            'otherLangUrl'  => '/home-nl',
        ];

        $this->assertEquals($expected, $frontendService->getLangSwitchVariables($pageLanguage));

        $pageLanguageNl = new PageLanguage;
        $pageLanguageNl->id = 2;
        $pageLanguageNl->active = 1;
        $pageLanguageNl->language_code = 'nl';
        $pageLanguageNl->name = 'page-nl';
        $pageLanguageNl->setSlug('page-nl');
        $pageLanguageNl->page_id = 1;
        $pageLanguageNl->save();

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
     * @return PageLanguage
     */
    private function createPageLanguage($page = null, $active = 1): PageLanguage
    {
        $pageLanguage = new PageLanguage();

        $pageLanguage->active = $active;
        $pageLanguage->page   = $page;

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
}
