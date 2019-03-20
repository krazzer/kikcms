<?php


namespace KikCMS\Classes\DataTable;


use Helpers\TestHelper;
use KikCMS\Classes\DataTable\Filter\FilterSelect;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Translator;
use KikCMS\Forms\UserForm;
use KikCMS\Models\User;
use KikCMS\Services\LanguageService;
use KikCMS\Services\ModelService;
use KikCMS\Services\TwigService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCmsCore\Services\DbService;
use Phalcon\Di;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTableTest extends TestCase
{
    public function testGetDefaultQuery()
    {
        $stub = $this->createMock(DbService::class);

        $stub->method('getAliasForModel')->willReturn(null);

        $di = new Di();

        $di->set('modelsManager', new Manager());
        $di->set('dbService', $stub);

        $dataTable = new TestableDataTable();
        $dataTable->setDI($di);

        $this->assertInstanceOf(Builder::class, $dataTable->testableGetDefaultQuery());
        $this->assertTrue(is_string($dataTable->testableGetDefaultQuery()->getFrom()));

        $stubWithAlias = $this->createMock(DbService::class);
        $stubWithAlias->method('getAliasForModel')->willReturn('a');

        $dataTable->dbService = $stubWithAlias;

        $this->assertTrue(is_array($dataTable->testableGetDefaultQuery()->getFrom()));
    }

    public function testAddFilter()
    {
        $dataTable = new TestableDataTable();

        $dataTable->addFilter(new FilterSelect('test', 'test', []));

        $this->assertCount(1, $dataTable->getCustomFilters());
    }

    public function testCanAdd()
    {
        $aclStub = $this->createMock(AccessControl::class);

        $aclStub->method('resourceExists')->willReturn(false);

        $dataTable      = new TestableDataTable();
        $dataTable->acl = $aclStub;

        $this->assertTrue($dataTable->canAdd());

        $aclStub2 = $this->createMock(AccessControl::class);
        $aclStub2->method('resourceExists')->willReturn(true);
        $aclStub2->method('allowed')->willReturn(true);

        $dataTable->acl = $aclStub2;
        $this->assertTrue($dataTable->canAdd());

        $aclStub3 = $this->createMock(AccessControl::class);
        $aclStub3->method('resourceExists')->willReturn(true);
        $aclStub3->method('allowed')->willReturn(false);

        $dataTable->acl = $aclStub3;
        $this->assertFalse($dataTable->canAdd());
    }

    public function testCanDelete()
    {
        $aclStub = $this->createMock(AccessControl::class);

        $aclStub->method('resourceExists')->willReturn(false);

        $dataTable      = new TestableDataTable();
        $dataTable->acl = $aclStub;

        $this->assertTrue($dataTable->canDelete());

        $aclStub2 = $this->createMock(AccessControl::class);
        $aclStub2->method('resourceExists')->willReturn(true);
        $aclStub2->method('allowed')->willReturn(true);

        $dataTable->acl = $aclStub2;
        $this->assertTrue($dataTable->canDelete());

        $aclStub3 = $this->createMock(AccessControl::class);
        $aclStub3->method('resourceExists')->willReturn(true);
        $aclStub3->method('allowed')->willReturn(false);

        $dataTable->acl = $aclStub3;
        $this->assertFalse($dataTable->canDelete());
    }

    public function testEdit()
    {
        $aclStub = $this->createMock(AccessControl::class);

        $aclStub->method('resourceExists')->willReturn(false);

        $dataTable      = new TestableDataTable();
        $dataTable->acl = $aclStub;

        $this->assertTrue($dataTable->canEdit());

        $aclStub2 = $this->createMock(AccessControl::class);
        $aclStub2->method('resourceExists')->willReturn(true);
        $aclStub2->method('allowed')->willReturn(true);

        $dataTable->acl = $aclStub2;
        $this->assertTrue($dataTable->canEdit());

        $aclStub3 = $this->createMock(AccessControl::class);
        $aclStub3->method('resourceExists')->willReturn(true);
        $aclStub3->method('allowed')->willReturn(false);

        $dataTable->acl = $aclStub3;
        $this->assertFalse($dataTable->canEdit());
    }

    public function testCheckCheckbox()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager);

        $userMock = $this->createMock(User::class);
        $userMock->method('save')->willReturn(true);

        $modelService = $this->createMock(ModelService::class);
        $modelService->method('getObject')->willReturn($userMock);

        $relationKeyService = $this->createMock(RelationKeyService::class);
        $relationKeyService->method('isRelationKey')->willReturn(true);
        $relationKeyService->expects($this->once())->method('set');

        $di->set('modelService', $modelService);
        $di->set('relationKeyService', $relationKeyService);

        $dataTable = new NonEditableDataTable();
        $dataTable->setDI($di);

        $this->assertFalse($dataTable->checkCheckbox(1, 'email', true));

        $dataTable = new EditableDataTable();
        $dataTable->setDI($di);
        $dataTable->setFilters((new DataTableFilters())->setLanguageCode('nl'));

        $this->assertTrue($dataTable->checkCheckbox(1, 'email', true));

        $userMock = $this->createMock(User::class);
        $userMock->method('save')->willReturn(false);
        $userMock->expects($this->once())->method('__set');

        $modelService = $this->createMock(ModelService::class);
        $modelService->method('getObject')->willReturn($userMock);

        $relationKeyService = $this->createMock(RelationKeyService::class);
        $relationKeyService->method('isRelationKey')->willReturn(false);
        $relationKeyService->expects($this->never())->method('set');

        $dataTable->modelService       = $modelService;
        $dataTable->relationKeyService = $relationKeyService;

        $this->assertFalse($dataTable->checkCheckbox(1, 'email', true));
    }

    public function testDelete()
    {
        /** @var DataTable|MockObject $datatableMock */
        $datatableMock = $this->getMockBuilder(TestableDataTable::class)
            ->setMethods(['canDelete', 'getModel'])
            ->getMock();

        $di = new Di();
        $di->set('modelsManager', new Manager());

        /** @var User|MockObject $userReturnMock */
        $userReturnMock = $this->getMockBuilder(User::class)
            ->setConstructorArgs([null, $di])
            ->setMethods(['delete'])
            ->getMock();

        $userReturnMock->expects($this->exactly(3))->method('delete')->willReturn(true);

        $modelServiceMock = $this->getMockBuilder(ModelService::class)
            ->setMethods(['getObjects'])
            ->getMock();

        $modelServiceMock->method('getObjects')->willReturn([$userReturnMock, $userReturnMock, $userReturnMock]);

        $datatableMock->modelService = $modelServiceMock;

        $datatableMock->expects($this->exactly(3))->method('canDelete')->willReturn(true);

        $datatableMock->delete([1, 2, 3]);
    }

    public function testFormatBoolean()
    {
        $dataTable = new TestableDataTable();

        $dataTable->translator = (new TestHelper)->getTranslator();

        $this->assertTrue(is_string($dataTable->formatBoolean(true)));
    }

    public function testFormatCheckbox()
    {
        $dataTable = new TestableDataTable();

        $tagMock = $this->getMockBuilder(Tag::class)->setMethods(['tagHtml'])->getMock();

        $attributes = [
            'type'     => 'checkbox',
            'class'    => 'table-checkbox',
            'data-col' => 'test',
            'checked'  => 'checked'
        ];

        $tagMock->expects($this->exactly(2))->method('tagHtml')->with('input', $attributes);
        $dataTable->tag = $tagMock;

        $dataTable->formatCheckbox(1, [], 'test');
        $dataTable->formatCheckbox(1, ['test' => 1], 'test');

        $tagMock = $this->getMockBuilder(Tag::class)->setMethods(['tagHtml'])->getMock();

        $attributes = [
            'type'     => 'checkbox',
            'class'    => 'table-checkbox',
            'data-col' => 'test',
        ];

        $tagMock->expects($this->once())->method('tagHtml')->with('input', $attributes);
        $dataTable->tag = $tagMock;

        $dataTable->formatCheckbox(0, [], 'test');
    }

    public function testFormatFinderImage()
    {
        $dataTable = new TestableDataTable();

        $tagMock = $this->getMockBuilder(Tag::class)->setMethods(['tagHtml'])->getMock();
        $urlMock = $this->getMockBuilder(Url::class)->setMethods(['get'])->getMock();
        $twigServiceMock = $this->getMockBuilder(TwigService::class)->setMethods(['mediaFile'])->getMock();

        $attributes = [
            'class'          => 'thumb',
            'data-url'       => 'url',
            'data-thumb-url' => 'url',
            'style'          => 'background-image: url(url)',
        ];

        $tagMock->expects($this->once())->method('tagHtml')->willReturn('url')->with('div', $attributes);
        $urlMock->expects($this->once())->method('get')->willReturn('url');
        $twigServiceMock->expects($this->once())->method('mediaFile')->willReturn('url');

        $dataTable->tag = $tagMock;
        $dataTable->url = $urlMock;
        $dataTable->twigService = $twigServiceMock;

        $dataTable->formatFinderImage(1);

        $this->assertEquals('', $dataTable->formatFinderImage(null));
    }

    public function testFormatValue()
    {
        $dataTable = new TestableDataTable();

        $dataTable->setFieldFormatting('field', function ($value) {
            return 'test' . $value;
        });

        $this->assertEquals('test1', $dataTable->formatValue('field', '1', []));
        $this->assertNull($dataTable->formatValue('field2', '1', []));
    }

    public function testGetAlias()
    {
        $dataTable = new TestableDataTable();

        $dbServiceMock = $this->createMock(DbService::class);

        $dbServiceMock->expects($this->once())->method('getAliasForModel');

        $dataTable->dbService = $dbServiceMock;

        $dataTable->getAlias();
    }

    public function testGetAliasedTableKey()
    {
        /** @var MockObject|DataTable $dataTableMock */
        $dataTableMock = $this->getMockBuilder(TestableDataTable::class)
            ->setMethods(['getAlias'])
            ->getMock();

        $dataTableMock->method('getAlias')->willReturn('a');

        $this->assertEquals('a.' . $dataTableMock::TABLE_KEY, $dataTableMock->getAliasedTableKey());

        /** @var MockObject|DataTable $dataTableMock */
        $dataTableMock = $this->getMockBuilder(TestableDataTable::class)
            ->setMethods(['getAlias'])
            ->getMock();

        $dataTableMock->method('getAlias')->willReturn(null);

        $this->assertEquals($dataTableMock::TABLE_KEY, $dataTableMock->getAliasedTableKey());
    }

    public function testGetFilters()
    {
        $languageServiceMock = $this->createMock(LanguageService::class);
        $languageServiceMock->expects($this->once())->method('getDefaultLanguageCode')->willReturn('nl');

        $dataTable = new TestableDataTable();

        $dataTable->languageService = $languageServiceMock;
        $dataTable->setFilters(new DataTableFilters());

        $dataTable->getFilters();
    }

    public function testGetLabels()
    {
        $translatorMock = $this->createMock(Translator::class);
        $translatorMock->expects($this->exactly(2))->method('tl')->willReturn('x');

        $dataTable = new TestableDataTable();

        $dataTable->translator = $translatorMock;

        $this->assertEquals(['x', 'x'], $dataTable->getLabels());
    }

    public function testGetAndSetLimit()
    {
        $dataTable = new TestableDataTable();

        $dataTable->setLimit(100);

        $this->assertEquals(100, $dataTable->getLimit());
    }

    public function testGetSearchAbleFields()
    {
        $dataTable = new TestableDataTable();

        $this->assertEquals(['test'], $dataTable->getSearchableFields());
    }

    public function testGetSortableField()
    {
        $dataTable = new TestableDataTable();

        $this->assertEquals('test', $dataTable->getSortableField());
    }

    public function testGetRearanger()
    {
        $dataTable = new TestableDataTable();

        $this->assertEquals(new Rearranger($dataTable), $dataTable->getRearranger());
    }

    public function testIsMultiLingual()
    {
        $dataTable = new TestableDataTable();

        $this->assertTrue($dataTable->isMultiLingual());
    }

    public function testIsSortable()
    {
        $dataTable = new TestableDataTable();

        $this->assertTrue($dataTable->isSortable());
    }

    public function testIsSortableNewFirst()
    {
        $dataTable = new TestableDataTable();

        $this->assertTrue($dataTable->isSortableNewFirst());
    }

    public function testInitializeDatatable()
    {
        /** @var MockObject|DataTable $dataTableMock */
        $dataTableMock = $this->getMockBuilder(TestableDataTable::class)
            ->setMethods(['getFormClass', 'initialize'])
            ->getMock();

        $dataTableMock->method('getFormClass')->willReturn(MagicForm::class);

        $dataTableMock->securitySingleToken = null;

        $dataTableMock->expects($this->once())->method('initialize');
        $dataTableMock->initializeDatatable(true);

        $dataTableMock->expects($this->never())->method('initialize');
        $dataTableMock->initializeDatatable(true);
    }
}

class Tag
{
    public function tagHtml()
    {
        return null;
    }
}

class MagicForm
{
    public function __set($x, $y)
    {
    }

    public function __get($x)
    {
    }

    public function __call($name, $arguments)
    {
        return $this;
    }
}

class TestableDataTable extends DataTable
{
    protected $searchableFields = ['test'];
    protected $sortableField    = 'test';
    protected $multiLingual     = true;
    protected $sortable         = true;
    protected $sortableNewFirst = true;

    public function __construct(?Filters $filters = null)
    {
        $filters = (new DataTableFilters)->setLanguageCode('nl');

        parent::__construct($filters);
    }

    public function getModel(): string
    {
        return User::class;
    }

    public function getFormClass(): string
    {
        return UserForm::class;
    }

    public function testableGetDefaultQuery()
    {
        return $this->getDefaultQuery();
    }

    public function formatBoolean($value): string
    {
        return parent::formatBoolean($value);
    }

    public function formatCheckbox($value, $rowData, $column)
    {
        return parent::formatCheckbox($value, $rowData, $column);
    }

    public function formatFinderImage($value): string
    {
        return parent::formatFinderImage($value);
    }

    protected function initialize()
    {
        // nothing as of yet
    }
}

class NonEditableDataTable extends TestableDataTable
{
    public function canEdit($id = null): bool
    {
        return false;
    }
}

class EditableDataTable extends TestableDataTable
{
    public function canEdit($id = null): bool
    {
        return true;
    }
}