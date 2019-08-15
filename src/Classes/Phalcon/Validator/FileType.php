<?php

namespace KikCMS\Classes\Phalcon\Validator;


use KikCMS\Models\File;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class FileType extends Validator
{
    const OPTION_FILETYPES = 'fileTypes';

    /**
     * Override to set allowed filetypes
     *
     * @var array
     */
    protected $fileTypes = [];

    /**
     * @inheritdoc
     */
    public function validate(Validation $validator, $field)
    {
        $value = $validator->getValue($field);

        if ( ! $value) {
            return true;
        }

        $allowedFileTypes = $this->getAllowedFileTypes();

        if ( ! $file = File::getById($value)) {
            return $this->addInvalidFileMessageAndReturnFalse($validator, $field);
        }

        if (in_array(strtolower($file->getExtension()), $allowedFileTypes)) {
            return true;
        }

        return $this->addInvalidFileMessageAndReturnFalse($validator, $field);
    }

    /**
     * @param Validation $validator
     * @param $field
     * @return bool
     */
    private function addInvalidFileMessageAndReturnFalse(Validation $validator, $field): bool
    {
        $message = $validator->getDefaultMessage('FileType');
        $message = str_replace(':types', implode(', ', $this->getAllowedFileTypes()), $message);

        $validator->appendMessage(new Message($message, $field));

        return false;
    }

    /**
     * @return array
     */
    private function getAllowedFileTypes(): array
    {
        if($fileTypes = $this->getOption(self::OPTION_FILETYPES)){
            return $fileTypes;
        }

        return $this->fileTypes;
    }
}