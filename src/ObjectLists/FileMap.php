<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\File;
use KikCmsCore\Classes\ObjectMap;

class FileMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return File|false
     */
    public function get($key): File|false
    {
        return parent::get($key);
    }

    /**
     * @return File|false
     */
    public function current(): File|false
    {
        return parent::current();
    }
}