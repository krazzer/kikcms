<?php

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\UserService;

/**
 * @property UserService $userService
 * @property CmsService $cmsService
 */
class LoginForm extends WebForm
{
    const FIELD_USERNAME = 'username';
    const FIELD_PASSWORD = 'password';

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(self::FIELD_USERNAME, 'E-mail adres');
        $this->addPasswordField(self::FIELD_PASSWORD, 'Wachtwoord');

        $this->setPlaceHolderAsLabel(true);
        $this->setSendButtonLabel('Inloggen');
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
    {
        $user = $this->userService->getByEmail($input[self::FIELD_USERNAME]);

        if( ! $user->password){
            $this->flash->notice($this->translator->tl('login.activate.message'));
            return $this->response->redirect('cms/login/reset');
        }

        $this->cmsService->cleanUpDiskCache();
        $this->userService->setLoggedIn($user->id);

        return $this->response->redirect('cms');
    }

    /**
     * @inheritdoc
     */
    protected function validate(array $input): ErrorContainer
    {
        $errorContainer = new ErrorContainer();

        $email    = $input[self::FIELD_USERNAME];
        $password = $input[self::FIELD_PASSWORD];

        if ( ! $this->userService->isValidOrNotActivatedYet($email, $password)) {
            $errorContainer->addFormError($this->translator->tl('login.failed'), [self::FIELD_USERNAME, self::FIELD_PASSWORD]);
            return $errorContainer;
        }

        $user = $this->userService->getByEmail($email);

        if($user->blocked){
            $errorContainer->addFormError($this->translator->tl('login.blocked'));
        }

        return $errorContainer;
    }
}