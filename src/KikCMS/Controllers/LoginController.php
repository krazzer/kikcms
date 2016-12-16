<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Forms\LoginForm;
use Phalcon\Mvc\Controller;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

class LoginController extends Controller
{
    public function indexAction()
    {
        $this->view->form = new LoginForm();
    }

    public function resetAction()
    {
        $passwordResetForm = new WebForm();

        $passwordResetForm->addTextField('email', 'E-mail adres', [new PresenceOf(), new Email()]);

        $passwordResetForm->setPlaceHolderAsLabel(true);
        $passwordResetForm->setSendLabel('Stuur wachtwoord reset link');

        $this->view->form = $passwordResetForm;
    }
}