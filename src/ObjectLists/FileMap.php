<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\File;
use KikCmsCore\Classes\ObjectMap;

class FileMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return File|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return File|false
     */
    public function current()
    {
        return parent::current();
    }
}