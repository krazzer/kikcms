<?php

namespace KikCMS\ObjectLists;


use KikCmsCore\Classes\ObjectMap;

class PlaceholderTable extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PlaceholderMap|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderMap|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderMap|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderMap|false
     */
    public function reverse()
    {
        return parent::reverse();
    }
}