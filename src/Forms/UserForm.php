<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\KikcmsUser;
use Phalcon\Validation\Validator\Email;

class UserForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return KikcmsUser::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(KikcmsUser::FIELD_EMAIL, $this->translator->tl('fields.email'), [new Email()]);
        $this->addCheckboxField(KikcmsUser::FIELD_ACTIVE, $this->translator->tl('fields.active'));
    }
}