<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\DataForm\FieldTransformer;


use DateTime;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Fields\DateField;

/**
 * @property Translator $translator;
 * @property DateField $field;
 */
class Date extends FieldTransformer
{
    /**
     * @inheritdoc
     */
    public function toStorage(mixed $value): string
    {
        $date = DateTime::createFromFormat($this->field->getFormat(), $value);

        return $date->format($this->field->getStorageFormat());
    }

    /**
     * @inheritdoc
     */
    public function toDisplay(mixed $value): string
    {
        $date = new DateTime($value);

        return $date->format($this->field->getFormat());
    }
}