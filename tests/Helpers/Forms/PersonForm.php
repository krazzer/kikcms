<?php


namespace Helpers\Forms;


use Helpers\DataTables\PersonInterests;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use Helpers\Models\Person;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class PersonForm extends DataForm
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
        $this->addTextField(Person::FIELD_NAME, 'Name', [new PresenceOf]);
        $this->addDataTableField('personInterests', PersonInterests::class, 'Person interests');
        $this->addDateField('created', 'Date created', [new PresenceOf]);
    }
}