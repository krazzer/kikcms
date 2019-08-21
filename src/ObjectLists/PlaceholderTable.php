<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCmsCore\Classes\ObjectMap;

class PlaceholderTable extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|PlaceholderMap|PlaceholderFileThumbUrlMap|PlaceholderFileUrlMap|false
     */
    public function reverse()
    {
        return parent::reverse();
    }
}