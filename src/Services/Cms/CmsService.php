<?php

namespace KikCMS\Services\Cms;


use KikCMS\Classes\Translator;
use KikCMS\Config\MenuConfig;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Di\Injectable;

/**
 * Contains some generic CMS functions
 *
 * @property Translator $translator
 * @property WebsiteService $websiteService
 */
class CmsService extends Injectable
{
    /**
     * @return CmsMenuGroup[]
     */
    public function getMenuItemGroups(): array
    {
        $groups = [];

        foreach (MenuConfig::MENU_STRUCTURE as $groupId => $menuItems) {
            $groupLabel = $this->translator->tlb('menu.group.' . $groupId);
            $menuItems  = $this->getMenuItems($menuItems);

            $menuGroup = (new CmsMenuGroup())
                ->setId($groupId)
                ->setLabel($groupLabel)
                ->setMenuItems($menuItems);

            $groups[$groupId] = $menuGroup;
        }

        return $this->websiteService->callMethod('CmsMenu', 'getMenuGroups', [$groups], false, $groups);
    }

    /**
     * @param array $menuItems [menuItemId => route]
     * @return CmsMenuItem[]
     */
    private function getMenuItems(array $menuItems): array
    {
        $menuItemObjects = [];

        foreach ($menuItems as $menuItemId => $route) {
            $label = $this->translator->tlb('menu.item.' . $menuItemId);

            $menuItemObject = new CmsMenuItem($menuItemId, $label, 'cms/' . $route);
            $menuItemObjects[$menuItemId] = $menuItemObject;
        }

        return $menuItemObjects;
    }
}