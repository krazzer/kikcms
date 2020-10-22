<?php
declare(strict_types=1);

namespace KikCMS\Classes\WebForm;


class FieldError
{
    /** @var string */
    private $field;

    /** @var string */
    private $message;

    /** @var bool */
    private $alert;

    /**
     * @param string $field
     * @param string $message
     * @param bool $alert
     */
    public function __construct(string $field, string $message, bool $alert = true)
    {
        $this->field   = $field;
        $this->message = $message;
        $this->alert   = $alert;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return FieldError
     */
    public function setField(string $field): FieldError
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return FieldError
     */
    public function setMessage(string $message): FieldError
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAlert(): bool
    {
        return $this->alert;
    }

    /**
     * @param bool $alert
     * @return FieldError
     */
    public function setAlert(bool $alert): FieldError
    {
        $this->alert = $alert;
        return $this;
    }


}