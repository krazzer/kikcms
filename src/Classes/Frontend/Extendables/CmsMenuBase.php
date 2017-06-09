<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Services\Cms\CmsMenuGroup;

/**
 * Extend this class in Website/CmsPage to add/change Items and Groups of the CmsMenu
 */
class CmsMenuBase extends WebsiteExtendable
{
    /**
     * @param CmsMenuGroup[] $menuGroups
     * @return CmsMenuGroup[]
     */
    public function getMenuGroups(array $menuGroups)
    {
        return $menuGroups;
    }
}