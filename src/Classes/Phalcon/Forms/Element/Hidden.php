<?php

namespace KikCMS\Classes\Phalcon\Forms\Element;

class Hidden extends \Phalcon\Forms\Element\Hidden
{
    /**
     * @inheritDoc
     */
    public function getValue()
    {
        $value = parent::getValue();

        if(is_array($value)){
            return json_encode($value);
        }

        return $value;
    }
}