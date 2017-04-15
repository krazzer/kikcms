<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;

class Wysiwyg extends Field
{
    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_WYSIWYG;
    }
}