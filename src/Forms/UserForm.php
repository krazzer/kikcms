<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\User;
use Phalcon\Validation\Validator\Email;

class UserForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return User::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(User::FIELD_EMAIL, $this->translator->tl('fields.email'), [new Email()]);
        $this->addCheckboxField(User::FIELD_BLOCKED, $this->translator->tl('fields.blocked'));
    }
}