<?php declare(strict_types=1);

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Models\User;
use KikCMS\Services\UserService;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;

/**
 * @property UserService $userService
 */
class PasswordResetForm extends WebForm
{
    /** @var User */
    private $user;

    /**
     * @param User $user
     * @return PasswordResetForm
     */
    public function setUser(User $user): PasswordResetForm
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $passwordStringLength = new StringLength(['min' => 8, 'max' => 30]);

        $password = new Regex(['pattern' => '/^([^ ]*)$/', 'message' => $this->translator->tl('login.reset.password.space')]);

        $this->addTextField('email', 'E-mail')->setDefault($this->user->email)->setAttribute('readonly', 'readonly');
        $this->addPasswordField('password', 'Nieuw wachtwoord', [$passwordStringLength, $password]);
        $this->addPasswordField('password_repeat', 'Herhaal wachtwoord', [
            new Identical([
                'value'   => $this->getElement('password')->getValue(),
                'message' => $this->translator->tl('webform.messages.passwordMismatch'),
            ]),
        ]);

        $this->setPlaceHolderAsLabel(true);
        $this->setSendButtonLabel('Nieuw wachtwoord instellen');
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input)
    {
        $succesMessage = $this->getSuccessMessage();

        $this->userService->storePassword($this->user, $input['password']);
        $this->flash->success($succesMessage);

        return $this->response->redirect('cms/login');
    }

    /**
     * @return string
     */
    private function getSuccessMessage(): string
    {
        if ( ! $this->userService->isLoggedIn()) {
            return $succesMessage = $this->translator->tl('login.reset.password.flash.default');
        }

        if ($this->user->getId() === $this->userService->getUserId()) {
            return $this->translator->tl('login.reset.password.flash.loggedIn');
        }

        return $this->translator->tl('login.reset.password.flash.loggedInOther', ['email' => $this->user->email]);
    }
}