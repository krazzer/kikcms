<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\TextArea;

class WysiwygField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new TextArea($key))
            ->setLabel($label)
            ->setAttribute('style', 'height: 350px')
            ->setAttribute('class', 'form-control wysiwyg')
            ->setAttribute('id', $key . '_' . uniqid())
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_WYSIWYG;
    }
}