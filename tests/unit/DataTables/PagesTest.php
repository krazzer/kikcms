<?php
declare(strict_types=1);

namespace unit\DataTables;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\DataTables\Filters\PagesDataTableFilters;
use KikCMS\DataTables\Pages;
use KikCMS\Forms\LinkForm;
use KikCMS\Forms\MenuForm;
use KikCMS\Forms\PageForm;
use KikCMS\Models\Page;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\Pages\TemplateService;
use ReflectionMethod;
use ReflectionProperty;
use Website\TestClasses\WebsiteSettings;

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

    public function testGetLabels()
    {
        $pages = new Pages();
        $pages->setFilters((new PagesDataTableFilters)->setLanguageCode('en'));
        $pages->translator = (new TestHelper)->getTranslator();

        $this->assertStringContainsString('page', $pages->getLabels()[0]);

        $pages->getFilters()->setPageType('menu');
        $this->assertStringContainsString('menu', $pages->getLabels()[0]);

        $pages->getFilters()->setPageType('link');
        $this->assertStringContainsString('link', $pages->getLabels()[0]);

        $pages->getFilters()->setPageType('alias');
        $this->assertStringContainsString('alias', $pages->getLabels()[0]);
    }

    public function testGetFormClass()
    {
        $pages = new Pages();
        $pages->setFilters((new PagesDataTableFilters)->setLanguageCode('en'));

        $template = new Template('key', 'name');
        $template->setForm('x');

        $pagesDataTableService = $this->createMock(PagesDataTableService::class);
        $pagesDataTableService->method('getTemplate')->willReturn($template);

        $pages->pagesDataTableService = $pagesDataTableService;

        $this->assertEquals('x', $pages->getFormClass());

        $pagesDataTableService = $this->createMock(PagesDataTableService::class);
        $pagesDataTableService->method('getTemplate')->willReturn(null);

        $pages->pagesDataTableService = $pagesDataTableService;

        $this->assertEquals(PageForm::class, $pages->getFormClass());

        $pages->getFilters()->setPageType('menu');
        $this->assertEquals(MenuForm::class, $pages->getFormClass());

        $pages->getFilters()->setPageType('link');
        $this->assertEquals(LinkForm::class, $pages->getFormClass());
    }

    public function testIsHidden()
    {
        $property = new ReflectionProperty(Pages::class, 'closedPageIdMapCache');
        $property->setAccessible(true);

        $filters = (new PagesDataTableFilters)
            ->setLanguageCode('en')
            ->setSearch('search');

        $pages = new Pages();

        $property->setValue($pages, [1 => [1]]);

        $pages->setFilters($filters);

        $this->assertFalse($pages->isHidden(1));

        $pages->setFilters(new PagesDataTableFilters);

        $this->assertTrue($pages->isHidden(1));

        $property->setValue($pages, [0 => [0]]);

        $this->assertFalse($pages->isHidden(1));
    }

    public function testGetDefaultQuery()
    {
        $method = new ReflectionMethod(Pages::class, 'getDefaultQuery');
        $method->setAccessible(true);

        $filters = (new PagesDataTableFilters)
            ->setLanguageCode('en')
            ->setSearch('search');

        $languageService = $this->createMock(LanguageService::class);
        $languageService->method('getDefaultLanguageCode')->willReturn('en');

        $websiteSettings = $this->createMock(WebsiteSettings::class);
        $websiteSettings->method('getPageClass')->willReturn(Page::class);

        $templateService = $this->createMock(TemplateService::class);
        $templateService->method('getAllowedKeys')->willReturn([]);

        $pages = new Pages();
        $pages->setFilters($filters);

        $pages->languageService = $languageService;
        $pages->websiteSettings = $websiteSettings;
        $pages->templateService = $templateService;

        $this->assertNotNull($method->invoke($pages));
    }

    public function testFormatName()
    {
        $method = new ReflectionMethod(Pages::class, 'formatName');
        $method->setAccessible(true);

        $filters = (new PagesDataTableFilters)
            ->setLanguageCode('en')
            ->setSearch('search');

        $pagesDataTableService = $this->createMock(PagesDataTableService::class);
        $pagesDataTableService->method('getValue')->willReturn('value');

        $pages = new Pages();
        $pages->setFilters($filters);

        $pages->pagesDataTableService = $pagesDataTableService;

        $this->assertEquals('value', $method->invoke($pages, 'value', []));
    }

    public function testGetClosedPageIdMap()
    {
        $property = new ReflectionProperty(Pages::class, 'closedPageIdMapCache');
        $property->setAccessible(true);

        $pages = new Pages();

        $property->setValue($pages, [1 => [1]]);

        $this->assertEquals([1 => [1]], $pages->getClosedPageIdMap());
    }

    public function testGetIconTitleMap()
    {
        $method = new ReflectionMethod(Pages::class, 'getIconTitleMap');
        $method->setAccessible(true);

        $pages = new Pages();

        $this->assertEquals(['link' => null, 'lock' => null], $method->invoke($pages, ['type' => 'link', 'key' => 1]));
        $this->assertEquals(['share-alt' => null], $method->invoke($pages, ['type' => 'alias', 'key' => null]));
        $this->assertEquals(['eye-close' => null], $method->invoke($pages, ['type' => 'page', 'key' => null, 'active' => null]));
    }
}
