<?php

namespace KikCMS\Services\Cms;

/**
 * Value Object for a CMS menu item
 */
class CmsMenuGroup
{
    /** @var string */
    private $id;

    /** @var CmsMenuItem[] */
    private $menuItems = [];

    /** @var string */
    private $label;

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
    public function add(CmsMenuItem $menuItem)
    {
        $menuItems = $this->getMenuItems();

        $menuItems[$menuItem->getId()] = $menuItem;

        $this->setMenuItems($menuItems);

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
     * @return CmsMenuItem[]
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    /**
     * @param CmsMenuItem[] $menuItems
     * @return CmsMenuGroup
     */
    public function setMenuItems(array $menuItems): CmsMenuGroup
    {
        $this->menuItems = $menuItems;
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