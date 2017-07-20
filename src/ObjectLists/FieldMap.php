<?php

namespace KikCMS\ObjectLists;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Util\ObjectMap;

class FieldMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return Field|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return Field|false
     */
    public function current()
    {
        return parent::current();
    }
}