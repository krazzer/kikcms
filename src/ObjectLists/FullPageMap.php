<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Classes\Frontend\FullPage;
use KikCmsCore\Classes\ObjectMap;

class FullPageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return FullPage|false
     */
    public function get($key)
    {
        return parent::get($key);
    }
    /**
     * @return FullPage|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return FullPage|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|FullPageMap|false
     */
    public function reverse()
    {
        return parent::reverse();
    }
}