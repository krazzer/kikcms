<?php

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Services\MailService;
use KikCMS\Services\UserService;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

/**
 * @property UserService $userService
 * @property MailService $mailService
 */
class PasswordResetLinkForm extends WebForm
{
    protected $sendButtonTranslationKey = 'login.reset.buttonLabel';

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $emailField = $this->addTextField('email', 'E-mail adres', [new PresenceOf(), new Email()]);

        if($email = $this->request->get('email')){
            $emailField->setDefault($email);
        }

        $this->setSendButtonLabel($this->translator->tl($this->sendButtonTranslationKey));
        $this->setPlaceHolderAsLabel(true);
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
    {
        $user = $this->userService->getByEmail($input['email']);

        if ( ! $user) {
            // pretend we send the mail, so the user won't know whether the given email adres exists or not
            $this->flash->success($this->translator->tl('login.reset.flash'));
            $_POST = [];
            return;
        }

        if ($this->userService->sendResetMail($user)) {
            $this->flash->success($this->translator->tl('login.reset.flash'));
            $_POST = [];
        } else {
            $this->flash->error($this->translator->tl('login.reset.error'));
        }
    }
}