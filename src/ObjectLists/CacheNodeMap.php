<?php

namespace KikCMS\ObjectLists;


use KikCMS\Classes\Cache\CacheNode;
use KikCmsCore\Classes\ObjectMap;

class CacheNodeMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return CacheNode|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return CacheNode|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return CacheNode|false
     */
    public function getLast()
    {
        return parent::getLast();
    }

    /**
     * @return CacheNode|false
     */
    public function current()
    {
        return parent::current();
    }
}