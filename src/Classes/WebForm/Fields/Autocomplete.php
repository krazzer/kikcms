<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Autocomplete extends Field
{
    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_AUTOCOMPLETE;
    }
}