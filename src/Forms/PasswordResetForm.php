<?php

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Models\User;
use KikCMS\Services\UserService;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

/**
 * @property UserService $userService
 */
class PasswordResetForm extends WebForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $user = User::getById($this->request->get('userId'));

        $passwordStringLength = new StringLength(['min' => 8, 'max' => 30]);

        $this->addTextField('email', 'E-mail')->setDefault($user->email)->setAttribute('readonly', 'readonly');
        $this->addPasswordField('password', 'Nieuw wachtwoord', [new PresenceOf(), $passwordStringLength]);
        $this->addPasswordField('password_repeat', 'Herhaal wachtwoord', [
            new PresenceOf(),
            new Identical([
                'value'   => $this->getElement('password')->getValue(),
                'message' => $this->translator->tl('webform.messages.passwordMismatch'),
            ]),
            $passwordStringLength
        ]);

        $this->setPlaceHolderAsLabel(true);
        $this->setSendButtonLabel('Nieuw wachtwoord instellen');
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
    {
        $userId        = $this->request->get('userId');
        $succesMessage = $this->translator->tl('login.reset.password.flash');

        $user = User::getById($userId);

        $this->userService->storePassword($user, $input['password']);
        $this->flash->success($succesMessage);
        return $this->response->redirect('cms/login');
    }
}