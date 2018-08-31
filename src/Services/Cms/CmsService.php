<?php

namespace KikCMS\Services\Cms;


use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SubDataTableNewIdsCache;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Users;
use KikCMS\Services\Website\WebsiteService;
use KikCmsCore\Classes\Model;
use Monolog\Logger;
use Phalcon\Cache\Backend;
use Phalcon\Di\Injectable;

/**
 * Contains some generic CMS functions
 *
 * @property Translator $translator
 * @property WebsiteService $websiteService
 * @property WebsiteSettingsBase $websiteSettings
 * @property AccessControl $acl
 * @property Backend $diskCache
 */
class CmsService extends Injectable
{
    /**
     * Clean up cache files saved on disk
     * These cleanup actions are of no interest to the user, so any error will be hidden, only logged
     */
    public function cleanUpDiskCache()
    {
        try {
            $diskCacheFolder = $this->diskCache->getOptions()['cacheDir'];

            $cacheFiles = glob($diskCacheFolder . '*');

            foreach ($cacheFiles as $file) {
                $fileName = basename($file);

                if (substr($fileName, 0, strlen(DataTable::INSTANCE_PREFIX)) !== DataTable::INSTANCE_PREFIX) {
                    continue;
                }

                if (filemtime($file) + (3600 * 24) > time()) {
                    continue;
                }

                $newIdsCache = unserialize($this->diskCache->get($fileName));

                if( ! $newIdsCache instanceof SubDataTableNewIdsCache) {
                    throw new Exception("Cache file $file could not be unserialized");
                }

                $this->removeUnsavedTemporaryRecords($newIdsCache);
                unlink($file);
            }
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            return;
        }
    }

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

        if ( ! $this->config->get('analytics') || ! $this->acl->allowed(Permission::ACCESS_STATISTICS)) {
            unset($groups[MenuConfig::MENU_GROUP_STATS]);
        }

        if ( ! $this->acl->allowed(Pages::class)) {
            if ( ! $this->acl->allowed(Permission::ACCESS_FINDER)) {
                unset($groups[MenuConfig::MENU_GROUP_CONTENT]);
            } else {
                $groups[MenuConfig::MENU_GROUP_CONTENT]->remove(MenuConfig::MENU_ITEM_PAGES);
                $groups[MenuConfig::MENU_GROUP_CONTENT]->remove(MenuConfig::MENU_ITEM_SETTINGS);
            }
        }

        if ( ! $this->acl->allowed(Users::class)) {
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
     * @param $cache
     */
    private function removeUnsavedTemporaryRecords(SubDataTableNewIdsCache $cache)
    {
        $model = $cache->getModel();

        /** @var Model $model */
        $model = new $model();

        foreach ($cache->getIds() as $id) {
            if($object = $model::findFirst([$cache->getColumn() . ' = 0 AND ' . DataTable::TABLE_KEY . ' = ' . $id])){
                $object->delete();
            }
        }
    }
}