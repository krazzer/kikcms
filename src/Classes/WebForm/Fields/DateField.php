<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Config\DateTimeConfig;
use KikCmsCore\Config\DbConfig;
use Phalcon\Forms\Element\Text;

class DateField extends Field
{
    /** @var string */
    private $format;

    /** @var string */
    private $storageFormat = DbConfig::SQL_DATE_FORMAT;

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new Text($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_DATE;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return DateField|$this
     */
    public function setFormat(string $format): DateField
    {
        $this->format = $format;

        $this->setAttribute('data-format', DateTimeConfig::phpToMoment($format));

        return $this;
    }

    /**
     * @return string
     */
    public function getStorageFormat(): string
    {
        return $this->storageFormat;
    }

    /**
     * @param string $storageFormat
     * @return DateField|$this
     */
    public function setStorageFormat(string $storageFormat): DateField
    {
        $this->storageFormat = $storageFormat;
        return $this;
    }
}