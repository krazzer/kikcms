<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Classes\CmsPlugin;
use KikCmsCore\Classes\ObjectList;

class CmsPluginList extends ObjectList
{
    /**
     * @param int|string $key
     * @return CmsPlugin|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return CmsPlugin|false
     */
    public function current()
    {
        return parent::current();
    }
}