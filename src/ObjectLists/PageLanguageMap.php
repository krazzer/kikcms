<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\PageLanguage;
use KikCMS\Util\ObjectMap;

class PageLanguageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PageLanguage|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return PageLanguage|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }
}