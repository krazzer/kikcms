<?php
declare(strict_types=1);

namespace KikCMS\Services\Pages;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Models\Page;

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
        $page->lft = 1;
        $page->rgt = 2;

        $this->assertFalse($pageService->requiresNesting($page));
    }

    public function testGetChildren()
    {
        $pageService = new PageService();
        $pageService->setDI($this->getDbDi());

        $page = new Page();
        $page->id = 1;
        $page->lft = 1;
        $page->rgt = 2;
        $page->save();

        $page2 = new Page();
        $page2->id = 2;
        $page2->parent_id = 1;
        $page2->save();

        $pageMap = $pageService->getChildren($page);

        $this->assertEquals([2], $pageMap->keys());
    }

    public function testGetByIdList()
    {
        $pageService = new PageService();
        $pageService->setDI($this->getDbDi());

        $page = new Page();
        $page->id = 1;
        $page->save();

        $page2 = new Page();
        $page2->id = 2;
        $page2->save();

        $pageMap = $pageService->getByIdList([1,2]);

        $this->assertEquals([1,2], $pageMap->keys());
    }
}
