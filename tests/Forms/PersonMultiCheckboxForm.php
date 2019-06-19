<?php


namespace Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use Models\Person;
use Phalcon\Validation\Validator\PresenceOf;

class PersonMultiCheckboxForm extends DataForm
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
        $options = [
            1 => 'Rockets',
            2 => 'Cars',
        ];

        $this->addTextField('name', 'Name', [new PresenceOf])->setDefault('test');
        $this->addMultiCheckboxField('personInterests:interest_id', 'Interests', $options)->setDefault([1,2]);
    }
}