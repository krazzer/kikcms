<?php

namespace KikCMS\Classes\Phalcon\Validator;

use Phalcon\Messages\Message;
use Phalcon\Validation;

class Numericality extends Validation\Validator\Numericality
{
    /**
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        $value = $validation->getValue($field);

        // value may not contain a space
        if(strstr($value, ' ')){
            $message = $this->getOption('message') ?: $validation->translator->tl('webform.messages.Numericality');

            $validation->appendMessage(new Message($message, $field));
            return false;
        }

        return parent::validate($validation, $field);
    }
}