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
    public function get($key): Language|false
    {
        return parent::get($key);
    }

    /**
     * @return Language|false
     */
    public function getFirst(): Language|false
    {
        return parent::getFirst();
    }

    /**
     * @return Language|false
     */
    public function getLast(): Language|false
    {
        return parent::getLast();
    }

    /**
     * @return Language|false
     */
    public function current(): Language|false
    {
        return parent::current();
    }
}