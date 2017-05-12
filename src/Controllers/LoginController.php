<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Translator;
use KikCMS\Forms\LoginForm;
use KikCMS\Forms\PasswordResetForm;
use KikCMS\Forms\PasswordResetLinkForm;
use KikCMS\Services\MailService;
use KikCMS\Services\UserService;
use Phalcon\Config;
use Phalcon\Http\Response;

/**
 * @property Translator $translator
 * @property MailService $mailService
 * @property UserService $userService
 * @property Config $applicationConfig
 */
class LoginController extends BaseController
{
    public function indexAction()
    {
        $loginForm = (new LoginForm())->render();

        if ($loginForm instanceof Response) {
            return $loginForm;
        }

        $this->view->form = $loginForm;

        return null;
    }

    public function resetAction()
    {
        $this->view->form = (new PasswordResetLinkForm())->render();
    }

    public function resetPasswordAction()
    {
        $userId = $this->request->get('userId');
        $hash   = $this->request->get('hash');
        $time   = $this->request->get('t');

        if ( ! $this->security->checkHash($userId . $time, $hash)) {
            $errorMessage = $this->translator->tl('login.reset.password.hashError');
            $this->flash->error($errorMessage);
            return $this->response->redirect('cms/login');
        }

        if ( ! $time || $time + 7200 < date('U')) {
            $errorMessage = $this->translator->tl('login.reset.password.expired');
            $this->flash->error($errorMessage);
            return $this->response->redirect('cms/login/reset');
        }

        $passwordForm = (new PasswordResetForm())->render();

        if ($passwordForm instanceof Response) {
            return $passwordForm;
        }

        $this->flash->notice($this->translator->tl('login.reset.password.formMessage'));

        $this->view->form = $passwordForm;
        return $this->view->pick('login/reset');
    }
}