<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\FinderFile;
use KikCmsCore\Classes\ObjectMap;

class FileMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return FinderFile|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return FinderFile|false
     */
    public function current()
    {
        return parent::current();
    }
}