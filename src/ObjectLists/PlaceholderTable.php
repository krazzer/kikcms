<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCmsCore\Classes\ObjectMap;

class PlaceholderTable extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function get($key): PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function getFirst(): PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function current(): PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function reverse(): ObjectMap|PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
    {
        return parent::reverse();
    }
}