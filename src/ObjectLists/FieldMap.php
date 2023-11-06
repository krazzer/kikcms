<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Tab;
use KikCmsCore\Classes\ObjectMap;

class FieldMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return Field|Tab|FieldTransformer|false
     */
    public function get($key): Field|Tab|FieldTransformer|false
    {
        return parent::get($key);
    }

    /**
     * @return Field|Tab|FieldTransformer|false
     */
    public function current(): Field|Tab|FieldTransformer|false
    {
        return parent::current();
    }
}