<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;

class CmsPluginsBase extends WebsiteExtendable
{
    /**
     * @return array
     */
    public function getPlugins(): array
    {
        return [];
    }
}