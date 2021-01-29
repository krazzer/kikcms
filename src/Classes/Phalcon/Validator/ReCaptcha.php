<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon\Validator;


use Phalcon\Messages\Message;
use Phalcon\Validation;
use Phalcon\Validation\AbstractValidator;

class ReCaptcha extends AbstractValidator
{
    /**
     * Executes the validation
     *
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        /** @var \ReCaptcha\ReCaptcha $reCaptcha */
        $reCaptcha = $validation->reCaptcha;

        $response = $reCaptcha->verify(@$_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if($response->isSuccess()){
            return true;
        }

        $validation->appendMessage(new Message($validation->translator->tl('webform.messages.reCaptcha'), $field));

        return false;
    }
}