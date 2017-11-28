<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldTransformer;


use DateTime;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Fields\DateField;
use KikCmsCore\Config\DbConfig;

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
        $date = DateTime::createFromFormat($this->getDisplayFormat(), $value);

        return $date->format(DbConfig::SQL_DATE_FORMAT);
    }

    /**
     * @inheritdoc
     */
    public function toDisplay($value)
    {
        $date = new DateTime($value);

        return $date->format($this->getDisplayFormat());
    }

    /**
     * @return string
     */
    private function getDisplayFormat(): string
    {
        return $this->translator->tl('system.phpDateFormat');
    }
}