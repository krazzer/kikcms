<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon\Validator;


use KikCMS\Models\File;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Messages\Message;

class FileType extends AbstractValidator
{
    const OPTION_FILETYPES = 'fileTypes';

    /** @var array Override to set allowed filetypes */
    protected array $fileTypes = [];

    /**
     * @inheritdoc
     */
    public function validate(Validation $validation, $field): bool
    {
        $value = $validation->getValue($field);

        if ( ! $value) {
            if ($this->getOption('allowEmpty')) {
                return true;
            } else {
                $message = $validation->translator->tl('webform.messages.FileEmpty');
                $validation->appendMessage(new Message($message, $field));
                return false;
            }
        }

        $allowedFileTypes = $this->getAllowedFileTypes();

        if ( ! $file = File::getById($value)) {
            return $this->addInvalidFileMessageAndReturnFalse($validation, $field);
        }

        if (in_array(strtolower($file->getExtension()), $allowedFileTypes)) {
            return true;
        }

        return $this->addInvalidFileMessageAndReturnFalse($validation, $field);
    }

    /**
     * @param Validation $validation
     * @param $field
     * @return bool
     */
    private function addInvalidFileMessageAndReturnFalse(Validation $validation, $field): bool
    {
        $types   = implode(', ', $this->getAllowedFileTypes());
        $message = $validation->translator->tl('webform.messages.FileType', ['types' => $types]);

        $validation->appendMessage(new Message($message, $field));

        return false;
    }

    /**
     * @return array
     */
    private function getAllowedFileTypes(): array
    {
        if ($fileTypes = $this->getOption(self::OPTION_FILETYPES)) {
            return $fileTypes;
        }

        return $this->fileTypes;
    }
}