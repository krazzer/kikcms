<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\ObjectLists\CmsPluginList;

class CmsPluginsBase extends WebsiteExtendable
{
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
}