<?php

namespace KikCMS\ObjectLists;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Util\ObjectMap;

class FieldMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Field|false
     */
    public function get($key)
    {
        return parent::get($key);
    }
}