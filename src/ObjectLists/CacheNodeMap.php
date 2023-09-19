<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Classes\Cache\CacheNode;
use KikCmsCore\Classes\ObjectMap;

class CacheNodeMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return CacheNode|false
     */
    public function get($key): CacheNode|false
    {
        return parent::get($key);
    }

    /**
     * @return CacheNode|false
     */
    public function getFirst(): CacheNode|false
    {
        return parent::getFirst();
    }

    /**
     * @return CacheNode|false
     */
    public function getLast(): CacheNode|false
    {
        return parent::getLast();
    }

    /**
     * @return CacheNode|false
     */
    public function current(): CacheNode|false
    {
        return parent::current();
    }
}