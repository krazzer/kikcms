<?php

namespace KikCMS\Forms;

use KikCMS\Classes\Db\DbWrapper;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Services\UserService;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

/**
 * @property DbWrapper $db
 * @property UserService $userService
 */
class LoginForm extends WebForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField('email', 'E-mail adres', [new PresenceOf(), new Email()]);
        $this->addPasswordField('password', 'Wachtwoord', [new PresenceOf()]);

        $this->setPlaceHolderAsLabel(true);
        $this->setSendLabel('Inloggen');
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
    {
        $user = $this->userService->getByEmail($input['email']);

        if( ! $this->userService->isActive($user)){
            $this->flash->notice($this->translator->tl('login.activate'));
            return $this->response->redirect('cms/login/reset');
        } else {
            return $this->response->redirect('cms');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validate(array $input): ErrorContainer
    {
        $errorContainer = new ErrorContainer();

        $email    = $input['email'];
        $password = $input['password'];

        if ( ! $this->userService->isValidOrNotActivatedYet($email, $password)) {
            $errorContainer->addFormError($this->translator->tl('login.failed'), ['email', 'password']);
        }

        return $errorContainer;
    }
}