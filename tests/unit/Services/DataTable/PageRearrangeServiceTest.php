<?php
declare(strict_types=1);

namespace Services\DataTable;

use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Services\DataTable\PageRearrangeService;

class PageRearrangeServiceTest extends Unit
{
    public function testCheckOrderIntegrity()
    {
        $pageRearrangeService = new PageRearrangeService();
        $pageRearrangeService->setDI($this->getDbDi());

        // no pages without display order, nothing happens
        $pageRearrangeService->checkOrderIntegrity();

        // page misses display_order
        $pageRearrangeService->dbService->insert(Page::class, ['id' => 1, 'type' => 'page']);
        $pageRearrangeService->dbService->insert(Page::class, ['id' => 2, 'type' => 'page', 'parent_id' => 1, 'lft' => 1, 'rgt' => 2]);

        $pageRearrangeService->checkOrderIntegrity();

        $page = Page::getById(2);

        $this->assertEquals(1, $page->getDisplayOrder());
    }
}
