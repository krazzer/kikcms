<?php

namespace KikCMS\Classes\Phalcon\Validator;


use DateTime;
use Phalcon\Validation;

/**
 * Extends Phalcons Date validation with the option to validate empty as ok
 */
class Date extends Validation\Validator\Date
{
    const OPTION_ALLOW_EMPTY = 'allowEmpty';

    /**
     * @param Validation $validation
     * @param string $field
     *
     * @return bool
     */
    public function validate(Validation $validation, $field)
    {
        $value = $validation->getValue($field);

        if ($this->getOption(self::OPTION_ALLOW_EMPTY) && empty($value)) {
            return true;
        }

        return parent::validate($validation, $field);
    }

    /**
     * Copied this method from Phalcon or we will get a private access error
     *
     * @param mixed $value
     * @param mixed $format
     * @return bool
     */
    public function checkDate($value, $format): bool
    {
        if ( ! is_string($value)) {
            return false;
        }

        DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();

        if ($errors["warning_count"] > 0 || $errors["error_count"] > 0) {
            return false;
        }

        return true;
    }
}