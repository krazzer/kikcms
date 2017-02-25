<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\MultiCheckbox as MultiCheckboxStorage;
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

    /**
     * @return FieldStorage|MultiCheckboxStorage
     */
    protected function getNewFieldStorage(): FieldStorage
    {
        return new MultiCheckboxStorage();
    }
}