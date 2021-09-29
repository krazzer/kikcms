<?php declare(strict_types=1);

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Cms\RememberMeService;
use KikCMS\Services\UserService;

/**
 * @property UserService $userService
 * @property CmsService $cmsService
 * @property RememberMeService $rememberMeService
 */
class LoginForm extends WebForm
{
    const FIELD_USERNAME = 'username';
    const FIELD_PASSWORD = 'password';
    const FIELD_REMEMBER = 'remember';

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(self::FIELD_USERNAME, $this->translator->tl('login.email'));
        $this->addPasswordField(self::FIELD_PASSWORD, $this->translator->tl('login.password'));
        $this->addCheckboxField(self::FIELD_REMEMBER, $this->translator->tl('login.rememberMe'))->setDefault(1);

        $this->setPlaceHolderAsLabel(true);
        $this->setSendButtonLabel($this->translator->tl('login.login'));
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
    {
        $user = $this->userService->getByEmail($input[self::FIELD_USERNAME]);

        if ( ! $user->password) {
            $this->flash->notice($this->translator->tl('login.activate.message'));
            return $this->response->redirect('cms/login/reset');
        }

        $this->userService->setLoggedIn($user->getId());

        if ($input[self::FIELD_REMEMBER]) {
            $this->rememberMeService->addToken();
        }

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

        if ($user->blocked) {
            $errorContainer->addFormError($this->translator->tl('login.blocked'));
        }

        return $errorContainer;
    }
}