<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\PageLanguage;
use KikCmsCore\Classes\ObjectList;

class PageLanguageList extends ObjectList
{
    /**
     * @inheritdoc
     * @return PageLanguage|false
     */
    public function get($key): PageLanguage|false
    {
        return parent::get($key);
    }

    /**
     * @return PageLanguage|false
     */
    public function getFirst(): PageLanguage|false
    {
        return parent::getFirst();
    }

    /**
     * @return PageLanguage|false
     */
    public function current(): PageLanguage|false
    {
        return parent::current();
    }
}