<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\Frontend\Menu;
use KikCMS\Config\CacheConfig;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\FullPageService;
use Phalcon\Di\Injectable;

/**
 * @property FullPageService $fullPageService
 * @property CacheService $cacheService
 */
class MenuService extends Injectable
{
    /**
     * Adds the FullPageMap to the Menu object, using the Menu's properties
     *
     * @param Menu $menu
     */
    public function addFullPageMap(Menu $menu)
    {
        $fullPageMap = $this->fullPageService->getMapByMenu($menu);

        $menu->setFullPageMap($fullPageMap);
    }

    /**
     * @param Menu $menu
     * @param string $prefix
     * @return string
     */
    public function getCacheKey(Menu $menu, $prefix = CacheConfig::MENU): string
    {
        return $this->cacheService->createKey($prefix,
            $menu->getMenuId(),
            $menu->getLanguageCode(),
            $menu->getMaxLevel(),
            $menu->getTemplate(),
            $menu->getRestrictTemplateId()
        );
    }
}