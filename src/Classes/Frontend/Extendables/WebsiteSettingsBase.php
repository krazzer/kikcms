<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\Cms\CmsMenuGroup;
use Phalcon\Mvc\Router\Group;

/**
 * Contains multiple settings to expand the Cms/Website:
 *
 * - Cms Menu structure
 * - Plugins to load
 * - Backend and Frontend routes
 * - Services load and overload
 */
class WebsiteSettingsBase extends WebsiteExtendable
{
    /**
     * @param CmsMenuGroup[] $menuGroups
     * @return CmsMenuGroup[]
     */
    public function getCmsMenuGroups(array $menuGroups)
    {
        return $menuGroups;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        return [];
    }

    /**
     * @return CmsPluginList
     */
    public function getPluginList(): CmsPluginList
    {
        $pluginsList = new CmsPluginList();

        $plugins = $this->getPlugins();

        foreach ($plugins as $plugin) {
            $pluginsList->add(new $plugin());
        }

        return $pluginsList;
    }

    /**
     * @param Group $backend
     */
    public function addBackendRoutes(Group $backend)
    {

    }

    /**
     * @param Group $backend
     */
    public function addFrontendRoutes(Group $backend)
    {

    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getOverloadedServices(): array
    {
        return [];
    }
}