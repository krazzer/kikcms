<?php declare(strict_types=1);


namespace KikCMS\Forms;

use KikCMS\Models\User;

/**
 * Re-use the password reset activate form to change only the label for account activation
 */
class PasswordResetLinkActivateForm extends PasswordResetLinkForm
{
    protected $sendButtonTranslationKey = 'login.activate.buttonLabel';

    /**
     * @param User $user
     * @return bool
     */
    protected function sendMail(User $user): bool
    {
        return $this->userService->sendActivationMail($user);
    }
}