<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Forms\LoginForm;
use KikCMS\Services\MailService;
use KikCMS\Services\UserService;
use Phalcon\Config;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

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
        $form = new LoginForm();

        $this->view->form = $form->render();
    }

    public function resetAction()
    {
        $passwordResetForm = new WebForm();

        $passwordResetForm->addTextField('email', 'E-mail adres', [new PresenceOf(), new Email()]);

        $passwordResetForm->setPlaceHolderAsLabel(true);
        $passwordResetForm->setSendLabel('Stuur wachtwoord reset link');

        $passwordResetForm->setSuccessAction(function ($input) {
            $this->sendResetLink($input);
        });

        $this->view->form = $passwordResetForm->render();
    }

    public function resetPasswordAction()
    {
        $userId = $this->request->get('userId');
        $hash   = $this->request->get('hash');

        if ( ! $this->security->checkHash($userId, $hash)) {
            $errorMessage = $this->translator->tl('login.reset.password.hashError');
            $this->flash->error($errorMessage);
            $this->response->redirect('cms/login');
        }

        $passwordStringLength = new StringLength(['min' => 8, 'max' => 30]);

        $passwordResetForm = new WebForm();
        $passwordResetForm->addPasswordField('password', 'Nieuw wachtwoord', [new PresenceOf(), $passwordStringLength]);
        $passwordResetForm->addPasswordField('password_repeat', 'Herhaal wachtwoord', [
            new PresenceOf(),
            new Identical([
                'value'   => $passwordResetForm->getField('password')->getValue(),
                'message' => $this->translator->tl('webform.messages.passwordMismatch'),
            ]),
            $passwordStringLength
        ]);

        $passwordResetForm->setPlaceHolderAsLabel(true);
        $passwordResetForm->setSendLabel('Nieuw wachtwoord instellen');

        $passwordResetForm->setSuccessAction(function ($input) use ($userId) {
            $succesMessage = $this->translator->tl('login.reset.password.flash');

            $this->userService->storePassword($userId, $input['password']);
            $this->flash->success($succesMessage);
            $this->response->redirect('cms/login');
        });

        $this->view->form = $passwordResetForm->render();
        $this->view->pick('login/reset');
    }

    /**
     * @param array $input
     */
    private function sendResetLink(array $input)
    {
        $email = $input['email'];
        $user  = $this->userService->getByEmail($email);

        if ( ! $user) {
            // pretend we send the mail, so the user won't know whether the given email adres exists or not
            $this->flash->success($this->translator->tl('login.reset.flash'));
            unset($_POST);
            return;
        }

        $subject     = $this->translator->tl('login.reset.mail.subject');
        $body        = $this->translator->tl('login.reset.mail.body');
        $buttonLabel = $this->translator->tl('login.reset.mail.buttonLabel');

        $hash     = $this->security->hash($user['id']);
        $resetUrl = $this->url->get('cms/login/reset-password') . '?userId=' . $user['id'] . '&hash=' . $hash;

        $parameters['buttons'] = [['url' => $resetUrl, 'label' => $buttonLabel]];

        if ($this->mailService->sendServiceMail($email, $subject, $body, $parameters)) {
            $this->flash->success($this->translator->tl('login.reset.flash'));
            unset($_POST);
        } else {
            $this->flash->error($this->translator->tl('login.reset.error'));
        }
    }
}