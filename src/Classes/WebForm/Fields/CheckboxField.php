<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Check;

class CheckboxField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new Check($key))
            ->setLabel($label)
            ->setAttribute('type', 'element')
            ->addValidators($validators);

        $this->element = $element;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_CHECKBOX;
    }
}