<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\PageLanguage;
use KikCMS\Util\ObjectMap;

class PageLanguageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return PageLanguage
     */
    public function get($key): PageLanguage
    {
        return parent::get($key);
    }

    /**
     * @return PageLanguage
     */
    public function getFirst(): PageLanguage
    {
        return parent::getFirst();
    }
}