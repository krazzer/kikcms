<?php
declare(strict_types=1);

namespace unit\DataTables\PagesFlat;

use Helpers\DataTables\PagesFlat;
use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\DataTables\Filters\PagesDataTableFilters;
use KikCMS\Forms\PageForm;
use KikCmsCore\Services\DbService;

class PagesFlatTest extends Unit
{
    public function testGetDefaultQuery()
    {
        $filters = new PagesDataTableFilters();
        $filters->setLanguageCode('en');

        $pagesFlat = new PagesFlat();
        $pagesFlat->setFilters($filters);

        $dbService = $this->createMock(DbService::class);
        $dbService->method('getAliasForModel')->willReturn('alias');

        $pagesFlat->dbService = $dbService;

        $this->assertNotNull($pagesFlat->getDefaultQuery());
    }

    public function testGetFormClass()
    {
        $pages = new PagesFlat();

        $this->assertEquals(PageForm::class, $pages->getFormClass());
    }

    public function testGetJsData()
    {
        $pages = new PagesFlat();

        $pages->translator = (new TestHelper)->getTranslator();

        $this->assertIsArray($pages->getJsData()['properties']);
    }
}
