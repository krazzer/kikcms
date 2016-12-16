<?php

namespace KikCMS\Forms;

use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class LoginFormX extends Form
{
    public function initialize()
    {
        // Email
        $name = new Text('email', ['placeholder' => 'E-mail adres', 'class' => 'form-control']);
        $name->setLabel('E-mail adres');
        $name->setFilters(['striptags', 'string']);
        $name->addValidators([
            new PresenceOf(['message' => 'E-mail is required']),
            new Email(['message' => 'E-mail is not valid'])
        ]);
        $this->add($name);

        // Password
        $password = new Password('password', ['placeholder' => 'Wachtwoord', 'class' => 'form-control']);
        $password->setLabel('Wachtwoord');
        $password->addValidators([
            new PresenceOf([
                'message' => 'Password is required'
            ])
        ]);
        $this->add($password);
    }
}