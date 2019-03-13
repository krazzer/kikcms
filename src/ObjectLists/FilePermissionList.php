<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\FilePermission;
use KikCmsCore\Classes\ObjectList;

class FilePermissionList extends ObjectList
{
    /**
     * @param int|string $key
     * @return FilePermission|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return FilePermission|false
     */
    public function current()
    {
        return parent::current();
    }
}