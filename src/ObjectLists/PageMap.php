<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\Page;
use KikCMS\Util\ObjectMap;

class PageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Page
     */
    public function get($key): Page
    {
        return parent::get($key);
    }
}