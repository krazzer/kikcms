<?php declare(strict_types=1);

namespace KikCMS\Objects;

use KikCMS\ObjectLists\MenuItemMap;

/**
 * Value Object for a CMS menu item
 */
class CmsMenuGroup
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var MenuItemMap */
    private $menuItemMap = [];

    /**
     * @param string $id
     * @param string $label
     */
    public function __construct(string $id, string $label)
    {
        $this->id    = $id;
        $this->label = $label;
    }

    /**
     * Add a menu item to the group
     *
     * @param CmsMenuItem $menuItem
     * @return $this|CmsMenuGroup
     */
    public function add(CmsMenuItem $menuItem): CmsMenuGroup|static
    {
        $this->getMenuItemMap()->add($menuItem, $menuItem->getId());
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return CmsMenuGroup
     */
    public function setId(string $id): CmsMenuGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return MenuItemMap
     */
    public function getMenuItemMap(): MenuItemMap
    {
        if( ! $this->menuItemMap){
            $this->menuItemMap = new MenuItemMap();
        }

        return $this->menuItemMap;
    }

    /**
     * @param MenuItemMap $menuItemMap
     * @return CmsMenuGroup
     */
    public function setMenuItemMap(MenuItemMap $menuItemMap): CmsMenuGroup
    {
        $this->menuItemMap = $menuItemMap;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return CmsMenuGroup
     */
    public function setLabel(string $label): CmsMenuGroup
    {
        $this->label = $label;
        return $this;
    }
}