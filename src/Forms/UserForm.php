<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\Phalcon\Validator\NewUniqueness;
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
        $id = $this->getObject()->id ?? null;

        $unique = (new NewUniqueness(['model' => new User, 'id' => $id, 'message' => 'E-mail adres is al in gebruik']));

        $this->addTextField(User::FIELD_EMAIL, $this->translator->tl('fields.email'), [new Email(), $unique]);
        $this->addSelectField(User::FIELD_ROLE, $this->translator->tl('fields.role'), $this->cmsService->getRoleMap());
        $this->addCheckboxField(User::FIELD_BLOCKED, $this->translator->tl('fields.blocked'));
    }
}