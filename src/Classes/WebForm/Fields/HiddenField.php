<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

class HiddenField extends Field
{
    /**
     * @param string $key
     * @param string $defaultValue
     */
    public function __construct(string $key, string $defaultValue)
    {
        $element = (new Hidden($key))
            ->setDefault($defaultValue)
            ->setAttribute('type', 'hidden');

        $this->element = $element;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_HIDDEN;
    }
}