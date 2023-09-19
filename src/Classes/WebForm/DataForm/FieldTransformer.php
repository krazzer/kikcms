<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\DataForm;

use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\Phalcon\Injectable;

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
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public abstract function toStorage(mixed $value): mixed;

    /**
     * @param mixed $value
     * @return mixed
     */
    public abstract function toDisplay(mixed $value): mixed;
}