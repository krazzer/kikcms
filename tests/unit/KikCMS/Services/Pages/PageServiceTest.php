<?php
declare(strict_types=1);

namespace KikCMS\Services\Pages;

use Codeception\Test\Unit;
use Helpers\TestHelper;
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
}
