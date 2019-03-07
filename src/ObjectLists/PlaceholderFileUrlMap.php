<?php

namespace KikCMS\ObjectLists;


use KikCMS\Objects\PlaceholderFileUrl;
use KikCmsCore\Classes\ObjectMap;

class PlaceholderFileUrlMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PlaceholderFileUrl|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderFileUrl|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderFileUrl|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderFileUrl|false
     */
    public function reverse()
    {
        return parent::reverse();
    }
}