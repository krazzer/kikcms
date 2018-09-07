<?php


namespace KikCMS\ObjectLists;


use KikCMS\Services\Cms\CmsMenuGroup;
use KikCmsCore\Classes\ObjectMap;

class MenuGroupMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return CmsMenuGroup|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return CmsMenuGroup|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return CmsMenuGroup|false
     */
    public function getLast()
    {
        return parent::getLast();
    }

    /**
     * @return CmsMenuGroup|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @param string $menuKey
     * @param string $itemKey
     * @return MenuGroupMap
     */
    public function removeItem(string $menuKey, string $itemKey): MenuGroupMap
    {
        $this->get($menuKey)->getMenuItemMap()->remove($itemKey);
        return $this;
    }
}