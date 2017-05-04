<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class MultiCheckbox extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_MULTI_CHECKBOX;
    }
}