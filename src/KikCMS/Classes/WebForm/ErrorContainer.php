<?php

namespace KikCMS\Classes\WebForm;

/**
 * A container with all errors a form produced
 */
class ErrorContainer
{
    /** @var array Errors that where relevant to the complete form input, not a single field */
    private $formErrors = [];

    /** @var array [fieldName => string[]] */
    private $fieldErrors = [];

    /** @var array all fields that have an error */
    private $fieldsWithErrors = [];

    /**
     * @param string $message
     * @param string $field
     * @param bool $addToGlobal set this true if you also want this to be shown as form error
     */
    public function addFieldError(string $field, string $message, $addToGlobal = false)
    {
        if( ! array_key_exists($field, $this->fieldErrors)){
            $this->fieldErrors[$field] = [];
        }

        $this->fieldErrors[$field][] = $message;
        $this->setFieldHasError($field);

        if($addToGlobal){
            $this->addFormError($message);
        }
    }

    /**
     * @param string $message
     * @param array $fields mark these fields as they have an error, but will not have a message of their own
     */
    public function addFormError(string $message, array $fields = [])
    {
        foreach ($fields as $field){
            $this->setFieldHasError($field);
        }

        $this->formErrors[] = $message;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function fieldHasError(string $field): bool
    {
        return in_array($field, $this->fieldsWithErrors);
    }

    /**
     * @param string $field
     * @return array
     */
    public function getErrorsForField(string $field): array
    {
        if( ! array_key_exists($field, $this->fieldErrors)){
            return [];
        }

        return $this->fieldErrors[$field];
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