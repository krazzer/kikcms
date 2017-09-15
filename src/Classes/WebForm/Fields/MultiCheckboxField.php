<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Check;

class MultiCheckboxField extends Field
{
    /** @var array */
    private $options = [];

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     */
    public function __construct(string $key, string $label, array $options)
    {
        $element = (new Check($key))
            ->setAttribute('type', 'multiCheckbox')
            ->setLabel($label);

        $this->element = $element;
        $this->options = $options;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool
    {
        return in_array($key, (array) $this->element->getValue());
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_MULTI_CHECKBOX;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return MultiCheckboxField
     */
    public function setOptions(array $options): MultiCheckboxField
    {
        $this->options = $options;
        return $this;
    }
}