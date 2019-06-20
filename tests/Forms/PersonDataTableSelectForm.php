<?php


namespace Forms;


use DataTables\PersonInterestsSelect;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use Models\Person;
use Phalcon\Validation\Validator\PresenceOf;

class PersonDataTableSelectForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Person::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField('name', 'Name', [new PresenceOf]);
        $this->addDataTableSelectField('personInterests:interest_id', new PersonInterestsSelect, 'Interests');
    }
}