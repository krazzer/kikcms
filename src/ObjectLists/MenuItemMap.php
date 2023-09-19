<?php declare(strict_types=1);


namespace KikCMS\ObjectLists;


use KikCMS\Objects\CmsMenuItem;
use KikCmsCore\Classes\ObjectMap;

class MenuItemMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return CmsMenuItem|false
     */
    public function get($key): CmsMenuItem|false
    {
        return parent::get($key);
    }

    /**
     * @return CmsMenuItem|false
     */
    public function getFirst(): CmsMenuItem|false
    {
        return parent::getFirst();
    }

    /**
     * @return CmsMenuItem|false
     */
    public function getLast(): CmsMenuItem|false
    {
        return parent::getLast();
    }

    /**
     * @return CmsMenuItem|false
     */
    public function current(): CmsMenuItem|false
    {
        return parent::current();
    }
}