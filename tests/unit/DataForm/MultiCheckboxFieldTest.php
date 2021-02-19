<?php


namespace unit\DataForm;


use Helpers\Forms\PersonMultiCheckboxForm;
use Helpers\TestHelper;
use Helpers\Models\Interest;
use Helpers\Models\Person;
use Helpers\Models\PersonInterest;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Security;
use PHPUnit\Framework\TestCase;

class MultiCheckboxFieldTest extends TestCase
{
    public function testSave()
    {
        $testDi = (new TestHelper)->getTestDi();

        $interest       = new Interest;
        $interest->id   = 1;
        $interest->name = 'Cars';
        $interest->save();

        $interest       = new Interest;
        $interest->id   = 2;
        $interest->name = 'Rockets';
        $interest->save();

        $form = new PersonMultiCheckboxForm();
        $form->setDI($testDi);

        $securityMock = $this->createMock(Security::class);
        $securityMock->method('checkToken')->willReturn(true);

        $form->security = $securityMock;

        $_POST = [
            $form->getFormId()             => $form->getFormId(),
            $form->security->getTokenKey() => $form->security->getToken(),
            'name'                         => 'Elon',
            'personInterests:interest_id'  => [1,2],
        ];

        $form->render();

        $query = (new Builder)
            ->columns([PersonInterest::FIELD_PERSON_ID, PersonInterest::FIELD_INTEREST_ID])
            ->from(PersonInterest::class);

        $personInterests = $form->dbService->getRows($query);

        $this->assertTrue($personInterests[0]['interest_id'] == 1);
        $this->assertTrue($personInterests[1]['interest_id'] == 2);

        $_POST = [
            $form->getFormId()             => $form->getFormId(),
            $form->security->getTokenKey() => $form->security->getToken(),
            'name'                         => 'Elon',
            'personInterests:interest_id'  => [],
        ];

        $filters = $form->getFilters();

        $form = new PersonMultiCheckboxForm();
        $form->setDI($testDi);
        $form->setFilters($filters);
        $form->security = $securityMock;

        $form->render();

        $query = (new Builder)
            ->columns([PersonInterest::FIELD_PERSON_ID, PersonInterest::FIELD_INTEREST_ID])
            ->from(PersonInterest::class);

        $personInterests = $form->dbService->getRows($query);

        $form->db->delete(Interest::TABLE);
        $form->db->delete(PersonInterest::TABLE);
        $form->db->delete(Person::TABLE);

        $this->assertEquals([], $personInterests);
    }
}