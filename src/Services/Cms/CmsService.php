<?php

namespace KikCMS\Services\Cms;


use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Users;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Di\Injectable;
use Website\Classes\WebsiteSettings;

/**
 * Contains some generic CMS functions
 *
 * @property Translator $translator
 * @property WebsiteService $websiteService
 * @property WebsiteSettings $websiteSettings
 * @property AccessControl $acl
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

        if( ! $this->config->get('analytics')){
            unset($groups[MenuConfig::MENU_GROUP_STATS]);
        }

        if( ! $this->acl->allowed(Pages::class)){
            unset($groups[MenuConfig::MENU_GROUP_CONTENT]);
        }

        if( ! $this->acl->allowed(Users::class)){
            $groups[MenuConfig::MENU_GROUP_CMS]->remove(MenuConfig::MENU_ITEM_USERS);
        }

        return $this->websiteSettings->getMenuGroups($groups);
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