<?php declare(strict_types=1);

namespace KikCMS\Forms;

use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Config\PassResetConfig;
use KikCMS\Models\User;
use KikCMS\Services\UserService;
use Phalcon\Filter\Validation\Validator\Identical;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Http\Response;

/**
 * @property UserService $userService
 * @property KeyValue $keyValue
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
    protected function initialize(): void
    {
        $passwordStringLength = new StringLength(['min' => 8, 'max' => 30]);

        $password = new Regex(['pattern' => '/^([^ ]*)$/', 'message' => $this->translator->tl('login.reset.password.space')]);

        $this->addTextField('email', $this->translator->tl('login.email'))->setDefault($this->user->email)->setAttribute('readonly', 'readonly');
        $this->addPasswordField('password', $this->translator->tl('login.reset.newPass'), [$passwordStringLength, $password]);
        $this->addPasswordField('password_repeat', $this->translator->tl('login.reset.repeatPass'), [
            new Identical([
                'value'   => $this->getElement('password')->getValue(),
                'message' => $this->translator->tl('webform.messages.passwordMismatch'),
            ]),
        ]);

        $this->setPlaceHolderAsLabel(true);
        $this->setSendButtonLabel($this->translator->tl('login.reset.resetButtonLabel'));
    }

    /**
     * @inheritdoc
     */
    protected function successAction(array $input): Response|string|null
    {
        $succesMessage = $this->getSuccessMessage();

        $this->keyValue->delete(PassResetConfig::PREFIX . $this->user->getId());
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
            return $this->translator->tl('login.reset.password.flash.default');
        }

        if ($this->user->getId() === $this->userService->getUserId()) {
            return $this->translator->tl('login.reset.password.flash.loggedIn');
        }

        return $this->translator->tl('login.reset.password.flash.loggedInOther', ['email' => $this->user->email]);
    }
}