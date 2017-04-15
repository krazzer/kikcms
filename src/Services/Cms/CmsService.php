<?php

namespace KikCMS\Services\Cms;


use KikCMS\Classes\Frontend\CmsMenuBase;
use KikCMS\Classes\Translator;
use KikCMS\Config\MenuConfig;
use Phalcon\Di\Injectable;

/**
 * Contains some generic CMS functions
 *
 * @property Translator $translator
 */
class CmsService extends Injectable
{
    /**
     * @return CmsMenuGroup[]
     */
    public function getMenuItemGroups(): array
    {
        $menuItemGroups = [];

        foreach (MenuConfig::MENU_STRUCTURE as $groupId => $menuItems) {
            $groupLabel = $this->translator->tl('menu.group.' . $groupId);
            $menuItems  = $this->getMenuItems($menuItems);

            $menuGroup = (new CmsMenuGroup())
                ->setId($groupId)
                ->setLabel($groupLabel)
                ->setMenuItems($menuItems);

            $menuItemGroups[$groupId] = $menuGroup;
        }

        return $this->loadWebsiteMenu($menuItemGroups);
    }

    /**
     * @param array $menuItems [menuItemId => route]
     * @return CmsMenuItem[]
     */
    private function getMenuItems(array $menuItems): array
    {
        $menuItemObjects = [];

        foreach ($menuItems as $menuItemId => $route) {
            $label = $this->translator->tl('menu.item.' . $menuItemId);

            $menuItemObject = new CmsMenuItem($menuItemId, $label, 'cms/' . $route);
            $menuItemObjects[$menuItemId] = $menuItemObject;
        }

        return $menuItemObjects;
    }

    /**
     * @param CmsMenuGroup[] $menuItemGroups
     * @return CmsMenuGroup[]
     */
    private function loadWebsiteMenu(array $menuItemGroups): array
    {
        $cmsMenuClass = 'Website\Classes\CmsMenu';

        if ( ! class_exists($cmsMenuClass)) {
            return $menuItemGroups;
        }

        /** @var CmsMenuBase $cmsMenu */
        $cmsMenu = new $cmsMenuClass();
        return $cmsMenu->getMenuGroups($menuItemGroups);
    }
}