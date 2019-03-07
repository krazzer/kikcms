<?php

namespace KikCMS\ObjectLists;


use KikCMS\Objects\PlaceholderFileThumbUrl;
use KikCmsCore\Classes\ObjectMap;

class PlaceholderFileThumbUrlMap extends PlaceholderFileUrlMap
{
    /**
     * @param int|string $key
     * @return PlaceholderFileThumbUrl|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderFileThumbUrl|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderFileThumbUrl|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderFileThumbUrl|false
     */
    public function reverse()
    {
        return parent::reverse();
    }
}