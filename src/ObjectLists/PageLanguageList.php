<?php

namespace KikCMS\ObjectLists;


use KikCMS\Models\PageLanguage;
use KikCmsCore\Classes\ObjectList;

class PageLanguageList extends ObjectList
{
    /**
     * @inheritdoc
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

    /**
     * @return PageLanguage|false
     */
    public function current()
    {
        return parent::current();
    }
}