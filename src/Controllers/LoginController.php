<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Translator;
use KikCMS\Forms\LoginForm;
use KikCMS\Forms\PasswordResetForm;
use KikCMS\Forms\PasswordResetLinkForm;
use KikCMS\Services\MailService;
use KikCMS\Services\UserService;
use Phalcon\Config;

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
        $this->view->form = (new LoginForm())->render();
    }

    public function resetAction()
    {
        $this->view->form = (new PasswordResetLinkForm())->render();
    }

    public function resetPasswordAction()
    {
        $userId = $this->request->get('userId');
        $hash   = $this->request->get('hash');

        if ( ! $this->security->checkHash($userId, $hash)) {
            $errorMessage = $this->translator->tlb('login.reset.password.hashError');
            $this->flash->error($errorMessage);
            $this->response->redirect('cms/login');
        }

        $this->view->form = (new PasswordResetForm())->render();
        $this->view->pick('login/reset');
    }
}