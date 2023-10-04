<?php

namespace KikCMS\Classes\Phalcon\Forms\Element;

class File extends \Phalcon\Forms\Element\File
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