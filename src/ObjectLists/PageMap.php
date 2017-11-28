<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\Page;
use KikCmsCore\Classes\ObjectMap;

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

    /**
     * @return Page|false
     */
    public function current()
    {
        return parent::current();
    }
}