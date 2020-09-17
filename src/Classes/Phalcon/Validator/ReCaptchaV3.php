<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon\Validator;


use Phalcon\Messages\Message;
use Phalcon\Validation;
use Phalcon\Validation\AbstractValidator;

class ReCaptchaV3 extends AbstractValidator
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
        $validation->appendMessage(new Message('Validation error', $field));
        return false;
    }
}