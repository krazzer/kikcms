<?php declare(strict_types=1);

namespace KikCMS\Services\Cms;


use DateTime;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SubDataTableNewIdsCache;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\MenuConfig;
use KikCMS\Config\PassResetConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Users;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\ObjectLists\MenuItemMap;
use KikCMS\Objects\CmsMenuGroup;
use KikCMS\Objects\CmsMenuItem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Contains some generic CMS functions
 */
class CmsService extends Injectable
{
    /**
     * Clean up cache files saved on disk
     */
    public function cleanUpDiskCache()
    {
        $cacheKeys = $this->keyValue->getAdapter()->getKeys(DataTable::INSTANCE_PREFIX);

        foreach ($cacheKeys as $cacheKey) {
            $newIdsCache = $this->keyValue->get($cacheKey);

            // invalid entry, delete and skip
            if( ! $newIdsCache instanceof SubDataTableNewIdsCache){
                $this->keyValue->delete($cacheKey);
                continue;
            }

            // entry must be older than 1 day
            if ($newIdsCache->getDate()->modify("+1 day") < new DateTime) {
                $this->removeUnsavedTemporaryRecords($newIdsCache);
                $this->keyValue->delete($cacheKey);
            }
        }

        // remove expired password reset tokens
        $passwordResetDir = $this->keyValue->getAdapter()->getStoragePath() . PassResetConfig::PREFIX;

        if(is_dir($passwordResetDir)) {
            $passwordResetTokenDir = new RecursiveDirectoryIterator($passwordResetDir);

            foreach (new RecursiveIteratorIterator($passwordResetTokenDir) as $filename => $cur) {
                if (date('U') - filemtime($filename) > PassResetConfig::LIFETIME) {
                    unlink($filename);
                }
            }
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

        if( ! $this->config->application->storeMailForms){
            $groupMap->removeItem(MenuConfig::MENU_GROUP_CONTENT, MenuConfig::MENU_ITEM_SENDFORMS);
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
        if ( ! class_exists($cache->getModel())) {
            return;
        }

        $column  = $cache->getColumn();
        $objects = $this->modelService->getObjects($cache->getModel(), $cache->getIds());

        foreach ($objects as $object) {
            if ($object->$column === '0' || $object->$column === 0) {
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

        $this->keyValue->set($token);

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
        if ( ! $token || ! $this->keyValue->has($token)) {
            throw new UnauthorizedException();
        }

        $this->keyValue->delete($token);
    }

    /**
     * @return string|null
     */
    public function getBaseUri(): ?string
    {
        if ($baseUri = $this->config->application->get('baseUri')) {
            return $baseUri;
        }

        if (@$this->request && $httpHost = $this->request->getServer('HTTP_HOST')) {
            return "https://" . $httpHost . '/';
        }

        $pathParts = explode('/', $this->config->application->path);

        // walk through the path to see if the domain name can be retrieved
        foreach ($pathParts as $part) {
            if (strstr($part, '.')) {
                return "https://" . $part . '/';
            }
        }

        return null;
    }
}