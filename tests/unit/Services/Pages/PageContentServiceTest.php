<?php
declare(strict_types=1);

namespace unit\Services\Pages;

use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Services\Pages\PageContentService;

class PageContentServiceTest extends Unit
{
    public function testGetPageVariable()
    {
        $di = $this->getDbDi();

        $pageContentService = new PageContentService();
        $pageContentService->setDI($di);

        $pageMock = $this->createMock(Page::class);
        $pageMock->method('getId')->willReturn(1);

        // test empty
        $this->assertNull($pageContentService->getPageVariable($pageMock, 'field'));

        // test empty
        $pageContentService->db->insert('cms_page_content', ['1', 'field', 'x'], ['page_id', 'field', 'value']);

        $this->assertEquals('x', $pageContentService->getPageVariable($pageMock, 'field'));
    }
}
