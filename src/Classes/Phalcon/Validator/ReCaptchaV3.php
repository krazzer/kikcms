<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon\Validator;


use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Messages\Message;

class ReCaptchaV3 extends AbstractValidator
{
    /**
     * Executes the validation
     *
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        if( ! $value = $validation->getValue($field)){
            return $this->returnError($validation, $field);
        }

        /** @var \ReCaptcha\ReCaptcha $reCaptcha */
        $reCaptcha = $validation->reCaptcha;

        $response = $reCaptcha->verify($value, $_SERVER['REMOTE_ADDR']);

        if( ! $response->isSuccess()){
            return $this->returnError($validation, $field);
        }

        $this->setOption('response', $response);

        return true;
    }

    /**
     * @param Validation $validation
     * @param $field
     * @return bool
     */
    private function returnError(Validation $validation, $field): bool
    {
        $validation->appendMessage(new Message($validation->translator->tl('webform.messages.reCaptchaV3Error'), $field));
        return false;
    }
}