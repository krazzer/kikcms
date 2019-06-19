<?php


namespace Integration\DataForm;


use Forms\PersonMultiCheckboxForm;
use Helpers\TestHelper;
use Models\Interest;
use Models\Person;
use Models\PersonInterest;
use Phalcon\Mvc\Model\Query\Builder;
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

        $form->db->delete(Interest::TABLE);
        $form->db->delete(PersonInterest::TABLE);
        $form->db->delete(Person::TABLE);
    }
}