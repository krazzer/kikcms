<?php

namespace KikCMS\Classes\Frontend;


use KikCMS\Services\Cms\CmsMenuGroup;
use Phalcon\Di\Injectable;

/**
 * Extend this class in Website/CmsPage to add/change Items and Groups of the CmsMenu
 */
abstract class CmsMenuBase extends Injectable
{
    /**
     * @param CmsMenuGroup[] $menuGroups
     * @return CmsMenuGroup[]
     */
    public abstract function getMenuGroups(array $menuGroups): array;
}