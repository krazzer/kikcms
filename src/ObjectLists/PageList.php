<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\Page;
use KikCmsCore\Classes\ObjectList;

class PageList extends ObjectList
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