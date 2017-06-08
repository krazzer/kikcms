<?php

namespace KikCMS\ObjectLists;


use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Util\ObjectMap;

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
}