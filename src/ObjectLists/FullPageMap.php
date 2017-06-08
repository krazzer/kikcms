<?php

namespace KikCMS\ObjectLists;


use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Util\ObjectMap;

class FullPageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return FullPage
     */
    public function get($key): FullPage
    {
        return parent::get($key);
    }
    /**
     * @return FullPage
     */
    public function getFirst(): FullPage
    {
        return parent::getFirst();
    }
}