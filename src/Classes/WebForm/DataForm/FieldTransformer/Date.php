<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldTransformer;


use DateTime;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Fields\DateField;

/**
 * @property Translator $translator;
 */
class Date extends FieldTransformer
{
    /** @var DateField */
    protected $field;

    /**
     * @inheritdoc
     */
    public function toStorage($value)
    {
        $date = DateTime::createFromFormat($this->field->getFormat(), $value);

        return $date->format($this->field->getStorageFormat());
    }

    /**
     * @inheritdoc
     */
    public function toDisplay($value)
    {
        $date = new DateTime($value);

        return $date->format($this->field->getFormat());
    }
}