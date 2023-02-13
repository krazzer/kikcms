<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon\Validator;


use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * Extends Uniqueness validator
 * If the provided id causing the uniqueness error, we can ignore it
 */
class NewUniqueness extends Uniqueness
{
    /**
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        if ($this->isUniqueness($validation, $field)) {
            return true;
        }

        $id    = $this->getOption('id');
        $model = $this->getOption('model');
        $attr  = $this->getOption('attribute');

        $fieldName = $attr ?: $field;

        if ( ! $object = $model::getById($id)) {
            return parent::validate($validation, $field);
        }

        if ($object->$fieldName == $validation->getValue($field)) {
            return true;
        }

        return parent::validate($validation, $field);
    }
}