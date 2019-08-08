<?php


namespace Website\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use Phalcon\Validation\Validator\PresenceOf;
use Website\Models\Person;

class PersonForm extends DataForm
{

    /**
     * @return string
     */
    public function getModel(): string
    {
        return Person::class;
    }

    /**
     * This method may contain logic that will influence the output when rendered
     */
    protected function initialize()
    {
        $this->addTextField(Person::FIELD_NAME, 'Name', [new PresenceOf]);
    }
}