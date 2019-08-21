<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Objects\Placeholder;
use KikCmsCore\Classes\ObjectMap;

class PlaceholderMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Placeholder|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return Placeholder|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return Placeholder|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|Placeholder|false
     */
    public function reverse()
    {
        return parent::reverse();
    }
}