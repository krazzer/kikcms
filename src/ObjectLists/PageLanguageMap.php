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
    public function getLast()
    {
        return parent::getLast();
    }

    /**
     * @return PageLanguage|false
     */
    public function current()
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