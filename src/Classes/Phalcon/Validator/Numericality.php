<?php

namespace KikCMS\Classes\Phalcon\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Messages\Message;

class Numericality extends Validation\Validator\Numericality
{
    /**
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        $value = $validation->getValue($field);

        // value may not contain a space
        if(str_contains($value, ' ')){
            $message = $this->getOption('message') ?: $validation->translator->tl('webform.messages.Numericality');

            $validation->appendMessage(new Message($message, $field));
            return false;
        }

        return parent::validate($validation, $field);
    }
}