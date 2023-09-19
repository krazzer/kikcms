<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\FilePermission;
use KikCmsCore\Classes\ObjectList;

class FilePermissionList extends ObjectList
{
    /**
     * @param int|string $key
     * @return FilePermission|false
     */
    public function get($key): FilePermission|false
    {
        return parent::get($key);
    }

    /**
     * @return FilePermission|false
     */
    public function current(): FilePermission|false
    {
        return parent::current();
    }
}