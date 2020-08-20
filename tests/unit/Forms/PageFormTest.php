<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\DataTables\PagesFlat;
use Helpers\Unit;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer\NameToId;
use KikCMS\Classes\WebForm\Fields\TextField;
use KikCMS\Classes\WebForm\Tab;
use KikCMS\Forms\PageForm;
use KikCMS\Models\PageLanguage;
use KikCMS\ObjectLists\FieldMap;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Validation;
use ReflectionMethod;

class PageFormTest extends Unit
{
    public function testValidate()
    {
        // can't edit menu
        $pageForm = new PageForm();

        $acl = $this->createMock(AccessControl::class);
        $acl->method('allowed')->willReturn(false);

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('xxx');

        $pageForm->acl        = $acl;
        $pageForm->translator = $translator;

        $result = $pageForm->validate(['type' => 'menu', 'pageLanguage*:slug' => '']);
        $this->assertTrue($result->hasFormErrors());

        // type is not menu or page
        $pageForm = new PageForm();

        $result = $pageForm->validate(['type' => 'link', 'pageLanguage*:slug' => '']);
        $this->assertFalse($result->hasFormErrors());

        // no slug
        $pageForm = new PageForm();

        $result = $pageForm->validate(['type' => 'page', 'pageLanguage*:slug' => '']);
        $this->assertFalse($result->hasFormErrors());

        // url path exists
        $pageForm = new PageForm();

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('urlPathExists')->willReturn(true);
        $urlService->expects($this->once())->method('getUrlByPageLanguage');

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('xxx');

        $parentPageLanguage = $this->createMock(PageLanguage::class);

        $pageLanguage = $this->createMock(PageLanguage::class);
        $pageLanguage->method('getParentWithSlug')->willReturn($parentPageLanguage);

        $pageLanguageService = $this->createMock(PageLanguageService::class);
        $pageLanguageService->method('getByPageId')->willReturn($pageLanguage);

        $pageForm->urlService          = $urlService;
        $pageForm->translator          = $translator;
        $pageForm->pageLanguageService = $pageLanguageService;

        $pageForm->getFilters()->setEditId(1);

        $result = $pageForm->validate(['type' => 'page', 'pageLanguage*:slug' => 'x']);

        $this->assertTrue($result->hasFieldErrors());
    }

    public function testInitialize()
    {
        $pagesFlat = $this->createMock(PagesFlat::class);

        $acl = $this->createMock(AccessControl::class);
        $acl->method('allowed')->willReturn(true);

        $pageForm = new PageForm();
        $pageForm->setDI($this->getDbDi());
        $pageForm->setDataTable($pagesFlat);

        $pageForm->acl = $acl;

        $method = new ReflectionMethod(PageForm::class, 'initialize');
        $method->setAccessible(true);

        $method->invoke($pageForm);
    }

    public function testAddFieldsForCurrentTemplate()
    {
        $pageForm = new PageForm();

        $fieldMap = new FieldMap([
            new Tab('tab', [new TextField('b', 'b')]),
            new TextField('a', 'b'),
            new NameToId(new TextField('a', 'b')),
        ]);

        $templateService = $this->createMock(TemplateService::class);
        $templateService->method('getFieldsByTemplate')->willReturn($fieldMap);

        $pagesDataTableService = $this->createMock(PagesDataTableService::class);
        $pagesDataTableService->method('getTemplate')->willReturn(new Template('a', 'b'));

        $pageForm->templateService       = $templateService;
        $pageForm->pagesDataTableService = $pagesDataTableService;
        $pageForm->validation            = new Validation();

        $method = new ReflectionMethod(PageForm::class, 'addFieldsForCurrentTemplate');
        $method->setAccessible(true);

        $method->invoke($pageForm);
    }
}
