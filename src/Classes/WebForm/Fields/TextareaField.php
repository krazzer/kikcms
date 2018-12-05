<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\TextArea;

class TextareaField extends Field
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
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_TEXTAREA;
    }

    /**
     * Shortcut to set the textarea's height
     *
     * @param int $rows
     * @return $this|TextareaField
     */
    public function rows(int $rows)
    {
        $style = $this->getElement()->getAttribute('style');
        $this->getElement()->setAttribute('style', $style . 'height: ' . (($rows * 19) + 16) . 'px;');

        return $this;
    }
}