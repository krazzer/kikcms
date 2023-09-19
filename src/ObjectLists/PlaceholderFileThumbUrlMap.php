<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Objects\PlaceholderFileThumbUrl;
use KikCmsCore\Classes\ObjectMap;

class PlaceholderFileThumbUrlMap extends PlaceholderFileUrlMap
{
    /**
     * @param int|string $key
     * @return PlaceholderFileThumbUrl|false
     */
    public function get($key): PlaceholderFileThumbUrl|false
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderFileThumbUrl|false
     */
    public function getFirst(): PlaceholderFileThumbUrl|false
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderFileThumbUrl|false
     */
    public function current(): PlaceholderFileThumbUrl|false
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderFileThumbUrl|false
     */
    public function reverse(): ObjectMap|PlaceholderFileThumbUrl|false
    {
        return parent::reverse();
    }
}