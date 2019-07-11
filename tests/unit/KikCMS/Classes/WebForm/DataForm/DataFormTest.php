<?php

namespace KikCMS\Classes\WebForm\DataForm;


use Helpers\DataTables\PersonInterests;
use Exception;
use Helpers\Forms\PersonForm;
use Helpers\TestHelper;
use KikCMS\Classes\DataTable\DataTableFilters;
use KikCMS\Services\ModelService;
use Helpers\Models\Company;
use Helpers\Models\Person;
use Helpers\Models\PersonInterest;
use PHPUnit\Framework\TestCase;

class DataFormTest extends TestCase
{
    public function testRender()
    {
        $di = (new TestHelper)->getTestDi();

        $personForm = new PersonForm();

        $personForm->setDI($di);

        $response = $personForm->render();

        $this->assertStringContainsString('<div class="webForm"', $response);
    }

    public function testGetDataTableFieldObjects()
    {
        $di = (new TestHelper)->getTestDi();

        $personForm = new PersonForm();
        $personForm->setDI($di);

        $person       = new Person();
        $person->id   = 1;
        $person->name = 'test';

        $company     = new Company();
        $company->id = 1;

        $person->company = $company;

        $person->save();

        $personForm->getFilters()->setEditId(1);

        // test isset
        $returnPerson = $personForm->getDataTableFieldObjects('company');

        $this->assertEquals(1, $returnPerson->id);

        // test no object present, and no cached ids
        $personForm = new PersonForm();
        $personForm->setDI($di);
        $personForm->addDataTableField('personInterests', PersonInterests::class, 'Person interests');

        $this->assertEquals([], $personForm->getDataTableFieldObjects('personInterests'));

        // test no object present, but with cached ids
        $personForm = new PersonForm();
        $personForm->setDI($di);
        $field = $personForm->addDataTableField('personInterests', PersonInterests::class, 'Person interests');

        $personInterest = new PersonInterest();

        $personInterest->id         = 128;
        $personInterest->company_id = 1;
        $personInterest->person_id  = 1;
        $personInterest->save();

        $dataTableFilters = (new DataTableFilters)
            ->setParentRelationKey('personInterests')
            ->setParentModel(Person::class);

        $field->getDataTable()->setFilters($dataTableFilters);
        $field->getDataTable()->cacheNewId(128);

        $result  = $personForm->getDataTableFieldObjects('personInterests');

        $this->assertInstanceOf(PersonInterest::class, $result[0]);
        $this->assertEquals(128, $result[0]->id);

        // remove dummies
        $person->delete();
        $company->delete();
        $personInterest->delete();

        // test exception
        $this->expectException(Exception::class);
        $personForm->getDataTableFieldObjects('nonExistingRelation');
    }

    public function testGetDataTableFieldObjectsFieldMissingException()
    {
        $di = (new TestHelper)->getTestDi();

        $personForm = new PersonForm;
        $personForm->setDI($di);

        $this->expectExceptionMessage("Field company does not exist");

        // not field fieldMap exception
        $personForm->getDataTableFieldObjects('company');
    }

    public function testGetObject()
    {
        $di = (new TestHelper)->getTestDi();

        $personForm = new PersonForm;
        $personForm->setDI($di);

        // test no id set
        $this->assertNull($personForm->getObject());

        $personMock = $this->createMock(Person::class);

        $modelServiceMock = $this->createMock(ModelService::class);
        $modelServiceMock->method('getObject')->willReturn($personMock);

        $personForm->getFilters()->setEditId(1);

        $personForm->modelService = $modelServiceMock;

        // test get object
        $this->assertEquals($personMock, $personForm->getObject());

        $personForm->setFilters(new DataFormFilters);

        // test cache
        $this->assertEquals($personMock, $personForm->getObject());
    }
}
