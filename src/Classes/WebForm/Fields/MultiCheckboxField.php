<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\Phalcon\FormElements\MultiCheck;
use KikCMS\Classes\WebForm\Field;

class MultiCheckboxField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $options
     */
    public function __construct(string $key, string $label, array $options)
    {
        $element = (new MultiCheck($key))
            ->setOptions($options)
            ->setAttribute('type', 'multiCheckbox')
            ->setLabel($label);

        $this->element = $element;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_MULTI_CHECKBOX;
    }
}