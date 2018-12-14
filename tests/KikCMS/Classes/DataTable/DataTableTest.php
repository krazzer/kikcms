<?php


namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\DataTable\Filter\FilterSelect;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Classes\Translator;
use KikCMS\Forms\UserForm;
use KikCMS\Models\User;
use KikCMS\Services\ModelService;
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

        /** @var Translator|MockObject $translatorMock */
        $translatorMock = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->setMethods(['tl'])
            ->getMock();

        $translatorMock->expects($this->once())->method('tl')->willReturn('string');

        $dataTable->translator = $translatorMock;

        $this->assertEquals('string', $dataTable->formatBoolean(true));
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

        $tagMock->expects($this->once())->method('tagHtml')->with('input', $attributes);
        $dataTable->tag = $tagMock;

        $dataTable->formatCheckbox(1, [], 'test');

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

        $attributes = [
            'class'          => 'thumb',
            'data-url'       => 'url',
            'data-thumb-url' => 'url',
            'style'          => 'background-image: url(url)',
        ];

        $tagMock->expects($this->once())->method('tagHtml')->willReturn('url')->with('div', $attributes);
        $urlMock->expects($this->exactly(2))->method('get')->willReturn('url');

        $dataTable->tag = $tagMock;
        $dataTable->url = $urlMock;

        $dataTable->formatFinderImage(1);

        $this->assertEquals('', $dataTable->formatFinderImage(null));
    }
}

class Tag
{
    public function tagHtml()
    {
        return null;
    }
}

class TestableDataTable extends DataTable
{
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