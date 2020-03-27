<?php
declare(strict_types=1);

namespace DataTables;

use Helpers\Unit;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\DataTables\Pages;
use KikCMS\Models\Page;
use KikCMS\Services\DataTable\PageRearrangeService;

class PagesTest extends Unit
{
    public function testDelete()
    {
        $pages = new Pages();
        $pages->setDI($this->getDbDi());

        $acl = $this->createMock(AccessControl::class);
        $acl->method('canDeleteMenu')->willReturn(false);
        $acl->method('resourceExists')->willReturn(false);

        $pageRearrangeService = $this->createMock(PageRearrangeService::class);
        $pageRearrangeService->method('updateNestedSet');
        $pageRearrangeService->expects($this->once())->method('updateLeftSiblingsOrder');

        $pages->acl = $acl;
        $pages->pageRearrangeService = $pageRearrangeService;

        $page1 = new Page();
        $page1->key = 'key';
        $page1->id = 1;

        $page2 = new Page();
        $page2->id = 2;
        $page2->type = Page::TYPE_MENU;

        $page3 = new Page();
        $page3->id = 3;

        $page1->save();
        $page2->save();
        $page3->save();

        $pages->delete([1,2,3]);
    }
}
