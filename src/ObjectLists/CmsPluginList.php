<?php

namespace KikCMS\ObjectLists;


use KikCMS\Classes\CmsPlugin;
use KikCMS\Util\ObjectList;

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
}