<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\User;
use KikCMS\Services\Cms\CmsService;
use Phalcon\Validation\Validator\Email;

/**
 * @property CmsService $cmsService
 */
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
        $this->addSelectField(User::FIELD_ROLE, $this->translator->tl('fields.role'), $this->cmsService->getRoleMap());
        $this->addCheckboxField(User::FIELD_BLOCKED, $this->translator->tl('fields.blocked'));
    }
}