<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\Page;
use KikCmsCore\Classes\ObjectMap;

class PageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Page|false
     */
    public function get($key): Page|false
    {
        return parent::get($key);
    }

    /**
     * @return Page|false
     */
    public function current(): Page|false
    {
        return parent::current();
    }
}