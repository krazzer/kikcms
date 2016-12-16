<?php

namespace KikCMS\Forms;

use KikCMS\Classes\WebForm\WebForm;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class LoginForm extends WebForm
{
    public function initialize()
    {
        $this->addTextField('email', 'E-mail adres', [new PresenceOf(), new Email()]);
        $this->addPasswordField('password', 'Wachtwoord', [new PresenceOf()]);

        $this->setPlaceHolderAsLabel(true);
        $this->setSendLabel('Inloggen');
    }
}