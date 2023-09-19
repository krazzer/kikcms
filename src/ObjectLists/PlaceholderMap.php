<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Objects\Placeholder;
use KikCmsCore\Classes\ObjectListInterface;
use KikCmsCore\Classes\ObjectMap;

class PlaceholderMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Placeholder|false
     */
    public function get($key): Placeholder|false
    {
        return parent::get($key);
    }

    /**
     * @return Placeholder|false
     */
    public function getFirst(): Placeholder|false
    {
        return parent::getFirst();
    }

    /**
     * @return Placeholder|false
     */
    public function current(): Placeholder|false
    {
        return parent::current();
    }

    /**
     * @return ObjectListInterface|ObjectMap|PlaceholderMap
     */
    public function reverse(): PlaceholderMap|ObjectListInterface|ObjectMap
    {
        return parent::reverse();
    }
}