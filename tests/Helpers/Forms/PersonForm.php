<?php


namespace Helpers\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use Helpers\Models\Person;
use Phalcon\Validation\Validator\PresenceOf;

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
    }
}