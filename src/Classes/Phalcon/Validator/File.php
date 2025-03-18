<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon\Validator;


use Phalcon\Filter\Validation;
use Phalcon\Messages\Message;

/**
 * Check if a file is actually send
 */
class File extends Validation\Validator\File
{
    /**
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        $value = $validation->getValue($field);

        if ( ! is_array($value)) {
            $validation->appendMessage(new Message('Invalid file', $field));
            return false;
        }

        return parent::validate($validation, $field);
    }
}