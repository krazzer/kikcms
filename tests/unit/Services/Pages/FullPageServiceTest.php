<?php
declare(strict_types=1);

namespace unit\Services\Pages;

use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\FullPageService;

class FullPageServiceTest extends Unit
{
    public function testGetByPage()
    {
        $fullPageService = new FullPageService();
        $fullPageService->setDI($this->getDbDi());

        $page      = new Page();
        $page->id  = 1;
        $page->lft = 1;
        $page->rgt = 2;

        // not found
        $this->assertNull($fullPageService->getByPage($page));

        // found
        $page->save();

        $pageLanguage                = new PageLanguage();
        $pageLanguage->page_id       = 1;
        $pageLanguage->language_code = 'en';
        $pageLanguage->name          = 'test';

        $pageLanguage->save();

        $fullPage = $fullPageService->getByPage($page);

        $this->assertEquals(1, $fullPage->getId());

        // with content
        $pageContent          = new PageContent();
        $pageContent->page_id = 1;
        $pageContent->value   = 'val';
        $pageContent->field   = 'key';

        $pageContent->save();

        $fullPage = $fullPageService->getByPage($page);

        $this->assertEquals('val', $fullPage->get('key'));
    }
}
