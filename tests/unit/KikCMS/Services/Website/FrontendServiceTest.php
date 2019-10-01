<?php

namespace KikCMS\Services\Website;


use Helpers\TestHelper;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit_Framework_TestCase;

class FrontendServiceTest extends PHPUnit_Framework_TestCase
{
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
