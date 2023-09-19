<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Classes\WebForm\Field;
use KikCmsCore\Classes\ObjectMap;

class FieldMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return Field|false
     */
    public function get($key): Field|false
    {
        return parent::get($key);
    }

    /**
     * @return Field|false
     */
    public function current(): Field|false
    {
        return parent::current();
    }
}