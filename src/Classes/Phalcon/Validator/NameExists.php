<?php

namespace KikCMS\Classes\Phalcon\Validator;


use KikCmsCore\Classes\Model;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class NameExists extends Validator
{
    const OPTION_MODEL = 'model';

    /**
     * @inheritdoc
     */
    public function validate(Validation $validator, $field)
    {
        $value = $validator->getValue($field);

        if( ! $value){
            return true;
        }

        /** @var Model $model */
        $model = $this->getOption(self::OPTION_MODEL);

        $object = $model::getByName($value);

        if ($object) {
            return true;
        }

        $validator->appendMessage(
            new Message($validator->getDefaultMessage('Default'), $field)
        );

        return false;
    }
}