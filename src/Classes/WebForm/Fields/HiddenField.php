<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

class HiddenField extends Field
{
    /**
     * @param string $key
     * @param string|null $defaultValue
     */
    public function __construct(string $key, string $defaultValue = null)
    {
        $element = (new Hidden($key))
            ->setDefault($defaultValue)
            ->setAttribute('type', 'hidden');

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_HIDDEN;
    }
}