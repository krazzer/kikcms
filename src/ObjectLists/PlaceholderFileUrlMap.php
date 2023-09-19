<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Objects\PlaceholderFileUrl;
use KikCmsCore\Classes\ObjectMap;

class PlaceholderFileUrlMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PlaceholderFileUrl|false
     */
    public function get($key): false|PlaceholderFileUrl
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderFileUrl|false
     */
    public function getFirst(): false|PlaceholderFileUrl
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderFileUrl|false
     */
    public function current(): false|PlaceholderFileUrl
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderFileUrl|false
     */
    public function reverse(): ObjectMap|false|PlaceholderFileUrl
    {
        return parent::reverse();
    }
}