<?php declare(strict_types=1);


namespace KikCMS\Forms;

/**
 * Re-use the password reset activate form to change only the label for account activation
 */
class PasswordResetLinkActivateForm extends PasswordResetLinkForm
{
    protected $sendButtonTranslationKey = 'login.activate.buttonLabel';
}