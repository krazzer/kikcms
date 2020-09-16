<?php
declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\Language;
use KikCmsCore\Classes\ObjectMap;

class LanguageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return Language|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return Language|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return Language|false
     */
    public function getLast()
    {
        return parent::getLast();
    }

    /**
     * @return Language|false
     */
    public function current()
    {
        return parent::current();
    }
}