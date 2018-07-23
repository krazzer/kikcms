<?php


namespace KikCMS\Classes\Phalcon\Validator;


use KikCMS\Classes\Translator;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class ReCaptcha extends Validator
{
    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string $field
     * @return bool
     */
    public function validate(Validation $validation, $field): bool
    {
        /** @var \ReCaptcha\ReCaptcha $reCaptcha */
        $reCaptcha = $validation->reCaptcha;

        /** @var Translator $translator */
        $translator = $validation->translator;

        $response = $reCaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if($response->isSuccess()){
            return true;
        }

        $validation->appendMessage(new Message($translator->tl('webform.messages.reCaptcha'), $field));

        return false;
    }
}