<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\Page;
use KikCMS\Util\ObjectMap;

class PageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Page|false
     */
    public function get($key)
    {
        return parent::get($key);
    }
}