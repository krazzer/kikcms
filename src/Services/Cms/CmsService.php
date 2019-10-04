<?php declare(strict_types=1);

namespace KikCMS\Services\Cms;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SubDataTableNewIdsCache;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Translator;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Users;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\ObjectLists\MenuItemMap;
use KikCMS\Objects\CmsMenuGroup;
use KikCMS\Objects\CmsMenuItem;
use KikCMS\Services\ModelService;
use KikCMS\Services\Website\WebsiteService;
use Monolog\Logger;
use Phalcon\Di\Injectable;

/**
 * Contains some generic CMS functions
 *
 * @property Translator $translator
 * @property WebsiteService $websiteService
 * @property WebsiteSettingsBase $websiteSettings
 * @property AccessControl $acl
 * @property KeyValue $keyValue
 * @property Logger $logger
 * @property ModelService $modelService
 */
class CmsService extends Injectable
{
    /**
     * Clean up cache files saved on disk
     */
    public function cleanUpDiskCache()
    {
        $cacheFiles      = $this->keyValue->queryKeys(DataTable::INSTANCE_PREFIX);
        $diskCacheFolder = $this->keyValue->getOptions()['cacheDir'];

        foreach ($cacheFiles as $fileName) {
            // file must be younger than 1 day
            if (filemtime($diskCacheFolder . $fileName) + CacheConfig::ONE_DAY > time()) {
                continue;
            }

            $newIdsCache = unserialize($this->keyValue->get($fileName));

            $this->removeUnsavedTemporaryRecords($newIdsCache);
        }
    }

    /**
     * @return MenuGroupMap
     */
    public function getMenuGroupMap(): MenuGroupMap
    {
        $groupMap = new MenuGroupMap();

        foreach (MenuConfig::MENU_STRUCTURE as $groupId => $menuItems) {
            $groupLabel  = $this->translator->tl('menu.group.' . $groupId);
            $menuItemMap = $this->getMenuItemMap($menuItems);

            $menuGroup = (new CmsMenuGroup($groupId, $groupLabel))->setMenuItemMap($menuItemMap);

            $groupMap->add($menuGroup, $groupId);
        }

        if ( ! $this->config->get('analytics') || ! $this->acl->allowed(Permission::ACCESS_STATISTICS)) {
            $groupMap->remove(MenuConfig::MENU_GROUP_STATS);
        }

        if ( ! $this->acl->allowed(Pages::class)) {
            if ( ! $this->acl->allowed(Permission::ACCESS_FINDER)) {
                $groupMap->remove(MenuConfig::MENU_GROUP_CONTENT);
            } else {
                $groupMap->removeItem(MenuConfig::MENU_GROUP_CONTENT, MenuConfig::MENU_ITEM_PAGES);
                $groupMap->removeItem(MenuConfig::MENU_GROUP_CONTENT, MenuConfig::MENU_ITEM_SETTINGS);
            }
        }

        if ( ! $this->acl->allowed(Users::class)) {
            $groupMap->removeItem(MenuConfig::MENU_GROUP_CMS, MenuConfig::MENU_ITEM_USERS);
        }

        return $this->websiteSettings->getMenuGroupMap($groupMap);
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

        foreach ($this->getRoles() as $roleKey) {
            $roleMap[$roleKey] = $this->translator->tl('cms.roles.' . $roleKey);
        }

        if ($removeVisitorRole) {
            unset($roleMap[Permission::VISITOR]);
        }

        return $roleMap;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        if ($roles = $this->websiteSettings->getRoles()) {
            return $roles;
        }

        return Permission::ROLES;
    }

    /**
     * @param array $menuItems [menuItemId => route]
     * @return MenuItemMap
     */
    private function getMenuItemMap(array $menuItems): MenuItemMap
    {
        $menuItemMap = new MenuItemMap();

        foreach ($menuItems as $menuItemId => $route) {
            $label = $this->translator->tl('menu.item.' . $menuItemId);

            $menuItemObject = new CmsMenuItem($menuItemId, $label, 'cms/' . $route);

            $menuItemMap->add($menuItemObject, $menuItemId);
        }

        return $menuItemMap;
    }

    /**
     * @param $cache
     */
    private function removeUnsavedTemporaryRecords(SubDataTableNewIdsCache $cache)
    {
        if( ! class_exists($cache->getModel())) {
            return;
        }

        $column  = $cache->getColumn();
        $objects = $this->modelService->getObjects($cache->getModel(), $cache->getIds());

        foreach ($objects as $object) {
            if ($object->$column === '0') {
                $object->delete();
            }
        }
    }

    /**
     * Create and store a security token
     *
     * @return string
     */
    public function createSecurityToken(): string
    {
        $token = uniqid('securityToken', true);

        $this->keyValue->save($token);

        return $token;
    }

    /**
     * Check if the token exists, remove it if it does, or else throw an exception
     *
     * @param string $token
     * @throws UnauthorizedException
     */
    public function checkSecurityToken(string $token)
    {
        if ( ! $this->keyValue->exists($token)) {
            throw new UnauthorizedException();
        }

        $this->keyValue->delete($token);
    }
}