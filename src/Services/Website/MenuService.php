<?php declare(strict_types=1);

namespace KikCMS\Services\Website;


use KikCMS\Classes\Frontend\Menu;
use KikCMS\Config\CacheConfig;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\FullPageService;
use KikCMS\Classes\Phalcon\Injectable;

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
    public function addFullPageMap(Menu $menu): void
    {
        $fullPageMap = $this->fullPageService->getMapByMenu($menu);

        $menu->setFullPageMap($fullPageMap);
    }

    /**
     * @param Menu $menu
     * @param string $prefix
     * @return string
     */
    public function getCacheKey(Menu $menu, string $prefix = CacheConfig::MENU): string
    {
        $encodedUlClass = preg_replace("/\W+/", "", str_replace('-', '_', $menu->getUlClass()));

        return $this->cacheService->createKey($prefix,
            $menu->getMenuKey(),
            $menu->getLanguageCode(),
            $menu->getMaxLevel(),
            $menu->getTemplate() ?: 'default',
            $menu->getUlClass() ? $encodedUlClass : 'null',
            $menu->getRestrictTemplates() ? implode('-', $menu->getRestrictTemplates()) : 'null'
        );
    }
}