<?php declare(strict_types=1);


namespace KikCMS\ObjectLists;


use KikCMS\Objects\CmsMenuGroup;
use KikCmsCore\Classes\ObjectMap;

class MenuGroupMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return CmsMenuGroup|false
     */
    public function get($key): CmsMenuGroup|false
    {
        return parent::get($key);
    }

    /**
     * @return CmsMenuGroup|false
     */
    public function getFirst(): CmsMenuGroup|false
    {
        return parent::getFirst();
    }

    /**
     * @return CmsMenuGroup|false
     */
    public function getLast(): CmsMenuGroup|false
    {
        return parent::getLast();
    }

    /**
     * @return CmsMenuGroup|false
     */
    public function current(): CmsMenuGroup|false
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