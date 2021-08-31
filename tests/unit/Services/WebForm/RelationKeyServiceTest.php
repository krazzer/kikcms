<?php
declare(strict_types=1);

namespace unit\Services\WebForm;

use Helpers\Models\PersonInterest;
use Helpers\Unit;
use KikCMS\Services\WebForm\RelationKeyService;
use Website\Models\Company;
use Website\Models\Person;
use Website\Models\PersonImage;

class RelationKeyServiceTest extends Unit
{
    public function testSet()
    {
        $relationKeyService = new RelationKeyService();
        $relationKeyService->setDI($this->getDbDi());

        $company = new Company();
        $company->name = 'CompanyX';
        $company->id = 1;
        $company->save();

        $person = new Person();
        $person->id = 1;
        $person->name = 'personX';
        $person->company_id = 1;
        $person->save();

        $personInterest = new PersonInterest();
        $personInterest->id = 1;
        $personInterest->person_id = 1;
        $personInterest->interest_id = 1;
        $personInterest->save();

        $person = Person::getById(1);
        $relationKeyService->set($person, 'company', 1);
        $person = Person::getById(1);
        $relationKeyService->set($person, 'company:person', 1);

        $relationKeyService->set($person, 'company:name', 'CompanyY');
        $this->assertEquals('CompanyY', $person->company->name);

        $relationKeyService->set($person, 'company:companyType:name', 'TypeX');
        $this->assertEquals('TypeX', $person->company->companyType->name);

        $person = Person::getById(1);
        $relationKeyService->set($person, 'personInterests:interest_id,grade', [1 => 10]);
        $relationKeyService->set($person, 'personInterests:interest_id,grade', [1 => null]);
    }

    public function testGet()
    {
        $relationKeyService = new RelationKeyService();
        $relationKeyService->setDI($this->getDbDi());

        $company = new Company();
        $company->name = 'CompanyX';
        $company->id = 1;
        $company->save();

        $person = new Person();
        $person->id = 1;
        $person->name = 'personX';
        $person->company_id = 1;

        $personInterest = new PersonInterest();
        $personInterest->interest_id = 5;
        $personInterest->grade = 10;

        $person->personInterests = [$personInterest];

        $person->save();

        $relationKeyService->get($person, 'company');
        $relationKeyService->get($person, 'company:person');
        $relationKeyService->get($person, 'company:person:company');
        $relationKeyService->get($person, 'company:person:company:person');
        $relationKeyService->get($person, 'company:person:company:person:company');
        $relationKeyService->get($person, 'company:person:company:person:company:person');

        $this->assertEquals([5 => 10], $relationKeyService->get($person, 'personInterests:interest_id,grade'));
    }
}
