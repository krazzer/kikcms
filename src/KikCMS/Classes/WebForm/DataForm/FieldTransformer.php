<?php

namespace KikCMS\Classes\WebForm\DataForm;
use KikCMS\Classes\WebForm\Field;
use Phalcon\Di\Injectable;

/**
 * Can be used to transform the value to its format in storage, and back
 */
abstract class FieldTransformer extends Injectable
{
    /** @var Field */
    protected $field;

    /**
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public abstract function toStorage($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    public abstract function toDisplay($value);
}