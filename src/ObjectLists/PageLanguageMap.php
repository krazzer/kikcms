<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\PageLanguage;
use KikCmsCore\Classes\ObjectMap;

class PageLanguageMap extends ObjectMap
{
    /**
     * @param int|string $key
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
    public function getLast(): PageLanguage|false
    {
        return parent::getLast();
    }

    /**
     * @return PageLanguage|false
     */
    public function current(): PageLanguage|false
    {
        return parent::current();
    }

    /**
     * @return string[]
     */
    public function getNameMap(): array
    {
        $nameMap = [];

        foreach ($this as $pageLanguage){
            $nameMap[] = $pageLanguage->getName();
        }

        return $nameMap;
    }
}