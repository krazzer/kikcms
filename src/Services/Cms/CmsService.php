<?php

namespace KikCMS\Services\Cms;


use KikCMS\Classes\Frontend\Extendables\CmsMenuBase;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Translator;
use KikCMS\Config\MenuConfig;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Di\Injectable;

/**
 * Contains some generic CMS functions
 *
 * @property Translator $translator
 * @property WebsiteService $websiteService
 * @property CmsMenuBase $cmsMenu
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
            $groupLabel = $this->translator->tl('menu.group.' . $groupId);
            $menuItems  = $this->getMenuItems($menuItems);

            $menuGroup = (new CmsMenuGroup($groupId, $groupLabel))
                ->setMenuItems($menuItems);

            $groups[$groupId] = $menuGroup;
        }

        return $this->cmsMenu->getMenuGroups($groups);
    }

    /**
     * Get roles mapped. [roleKey => translatedRoleName]
     *
     * @param bool $removeVisitorRole
     * @return array
     */
    public function getRoleMap($removeVisitorRole = true): array
    {
        $roleMap = [];

        foreach (Permission::ROLES as $roleKey){
            $roleMap[$roleKey] = $this->translator->tl('cms.roles.' . $roleKey);
        }

        if($removeVisitorRole){
            unset($roleMap[Permission::VISITOR]);
        }

        return $roleMap;
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
}