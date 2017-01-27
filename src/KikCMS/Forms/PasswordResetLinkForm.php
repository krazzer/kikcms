<?php

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Services\UserService;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

/**
 * @property UserService $userService
 */
class PasswordResetLinkForm extends WebForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField('email', 'E-mail adres', [new PresenceOf(), new Email()]);

        $this->setSendLabel('Stuur wachtwoord reset link');
        $this->setPlaceHolderAsLabel(true);
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
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