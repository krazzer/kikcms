<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm;

/**
 * A container with all errors a form produced
 */
class ErrorContainer
{
    /** @var array Errors that where relevant to the complete form input, not a single field */
    private $formErrors = [];

    /** @var array [field => FieldError[]] */
    private $fieldErrors = [];

    /** @var array all fields that have an error */
    private $fieldsWithErrors = [];

    /**
     * @param FieldError $fieldError
     * @param bool $addToGlobal set this true if you also want this to be shown as form error
     * @return ErrorContainer
     */
    public function addFieldError(FieldError $fieldError, $addToGlobal = false): ErrorContainer
    {
        $field = $fieldError->getField();

        if( ! array_key_exists($field, $this->fieldErrors)){
            $this->fieldErrors[$field] = [];
        }

        $this->fieldErrors[$field][] = $fieldError;
        $this->setFieldHasError($field);

        if($addToGlobal){
            $this->addFormError($fieldError->getMessage());
        }

        return $this;
    }

    /**
     * @param string $message
     * @param array $fields mark these fields as they have an error, but will not have a message of their own
     * @return ErrorContainer
     */
    public function addFormError(string $message, array $fields = []): ErrorContainer
    {
        foreach ($fields as $field){
            $this->setFieldHasError($field);
        }

        $this->formErrors[] = $message;

        return $this;
    }

    /**
     * @param Field $field
     * @return bool
     */
    public function fieldHasError(Field $field): bool
    {
        return in_array($field->getKey(), $this->fieldsWithErrors);
    }

    /**
     * @param Field $field
     * @return FieldError[]
     */
    public function getErrorsForField(Field $field): array
    {
        $fieldKey = $field->getKey();

        if ( ! array_key_exists($fieldKey, $this->fieldErrors)) {
            return [];
        }

        return $this->fieldErrors[$fieldKey];
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getAlertErrorMessagesForField(Field $field): array
    {
        $messages = [];

        $errors = $this->getErrorsForField($field);

        foreach ($errors as $error){
            if($error->isAlert()){
                $messages[] = $error->getMessage();
            }
        }

        return $messages;
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getTitleErrorMessagesForField(Field $field): array
    {
        $messages = [];

        $errors = $this->getErrorsForField($field);

        foreach ($errors as $error){
            if( ! $error->isAlert()){
                $messages[] = $error->getMessage();
            }
        }

        return $messages;
    }

    /**
     * @return array
     */
    public function getFormErrors(): array
    {
        return $this->formErrors;
    }

    /**
     * @return array
     */
    public function getFieldErrors(): array
    {
        return $this->fieldErrors;
    }

    /**
     * @return bool
     */
    public function hasFormErrors(): bool
    {
        return ! empty($this->getFormErrors());
    }

    /**
     * @return bool
     */
    public function hasFieldErrors(): bool
    {
        return ! empty($this->getFieldErrors());
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getFieldErrors()) && !$this->hasFormErrors();
    }

    /**
     * @param string $field
     */
    private function setFieldHasError(string $field)
    {
        if( ! in_array($field, $this->fieldsWithErrors)) {
            $this->fieldsWithErrors[] = $field;
        }
    }
}