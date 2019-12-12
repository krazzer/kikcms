<?php
declare(strict_types=1);

namespace KikCMS\Services\Pages;

use Exception;
use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\ObjectLists\PageMap;

class PageServiceTest extends Unit
{
    public function testRequiresNesting()
    {
        $testDi = (new TestHelper)->getTestDi();
        $page   = new Page();

        $page->setDI($testDi);

        $pageService = new PageService();

        // no parent
        $this->assertFalse($pageService->requiresNesting($page));

        // parent
        $page->parent_id = 1;
        $this->assertTrue($pageService->requiresNesting($page));

        // parent, and lft and rgt
        $page->parent_id = 1;
        $page->lft       = 1;
        $page->rgt       = 2;

        $this->assertFalse($pageService->requiresNesting($page));
    }

    public function testGetChildren()
    {
        $pageService = new PageService();
        $pageService->setDI($this->getDbDi());

        $page      = new Page();
        $page->id  = 1;
        $page->lft = 1;
        $page->rgt = 2;
        $page->save();

        $page2            = new Page();
        $page2->id        = 2;
        $page2->parent_id = 1;
        $page2->save();

        $pageMap = $pageService->getChildren($page);

        $this->assertEquals([2], $pageMap->keys());
    }

    public function testGetByIdList()
    {
        $pageService = new PageService();
        $pageService->setDI($this->getDbDi());

        $page     = new Page();
        $page->id = 1;
        $page->save();

        $page2     = new Page();
        $page2->id = 2;
        $page2->save();

        $pageMap = $pageService->getByIdList([1, 2]);

        $this->assertEquals([1, 2], $pageMap->keys());
    }

    public function testGetSelect()
    {
        $pageService = new PageService();
        $pageService->setDI($this->getDbDi());

        $this->addDefaultLanguage();

        $pageMap = new PageMap([
            1 => $this->createPage('page1', 1),
            2 => $this->createPage('page2', 2),
            3 => $this->createPage('subPage1', 3, 1),
            4 => $this->createPage('subPage2', 4, 1),
        ]);

        $select = $pageService->getSelect(0, $pageMap);

        $expected = [
            1 => "• page1",
            3 => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ο subPage1",
            4 => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ο subPage2",
            2 => "• page2"
        ];

        $this->assertEquals($expected, $select);
    }

    /**
     * @param string $name
     * @param int $id
     * @param int $parent
     * @return Page
     * @throws Exception
     */
    private function createPage(string $name, int $id, int $parent = 0): Page
    {
        $page            = new Page();
        $page->parent_id = $parent;
        $page->type      = Page::TYPE_PAGE;
        $page->id        = $id;
        $page->lft       = 0;
        $page->rgt       = 0;

        $pageLanguage                = new PageLanguage();
        $pageLanguage->name          = $name;
        $pageLanguage->page          = $page;
        $pageLanguage->language_code = 'en';

        $pageLanguage->save();

        return $page;
    }
}
