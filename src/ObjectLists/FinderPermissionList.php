<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\FinderPermission;
use KikCmsCore\Classes\ObjectList;

class FinderPermissionList extends ObjectList
{
    /**
     * @param int|string $key
     * @return FinderPermission|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return FinderPermission|false
     */
    public function current()
    {
        return parent::current();
    }
}