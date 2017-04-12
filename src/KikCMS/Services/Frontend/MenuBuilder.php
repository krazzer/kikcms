<?php

namespace KikCMS\Services\Frontend;


use KikCMS\Config\CacheConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Di\Injectable;

/**
 * @property UrlService $urlService
 * @property PageService $pageService
 * @property PageLanguageService $pageLanguageService
 * @property CacheService $cacheService
 */
class MenuBuilder extends Injectable
{
    /** @var string */
    private $languageCode;

    /**
     * @param string $languageCode
     */
    public function __construct(string $languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * Build a multi-level ul li structured menu
     *
     * @param int $menuId
     * @return string
     */
    public function buildMenu(int $menuId): string
    {
        $cacheKey = CacheConfig::MENU . ':' . $menuId . $this->languageCode;

        return $this->cacheService->cache($cacheKey, function() use ($menuId){
            if ( ! $menu = Page::getById($menuId)){
                return '';
            }

            $pageMap         = $this->pageService->getChildren($menu);
            $pageLanguageMap = $this->pageLanguageService->getByPageMap($pageMap, $this->languageCode);

            return $this->buildMenuHtml($menu, $pageMap, $pageLanguageMap);
        });
    }

    /**
     * @param Page $parentPage
     * @param Page[] $pageMap
     * @param PageLanguage[] $pageLanguageMap
     * @return string
     */
    private function buildMenuHtml(Page $parentPage, array $pageMap, array $pageLanguageMap): string
    {
        $menuOutput = '';

        /** @var Page $page */
        foreach ($pageMap as $page) {
            if ($page->parent_id != $parentPage->getId()) {
                continue;
            }

            $pageLanguage = $pageLanguageMap[$page->getId()];

            $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

            $menuOutput .= '<li><a href="' . $url . '">' . $pageLanguage->name . '</a>';
            $menuOutput .= $this->buildMenuHtml($page, $pageMap, $pageLanguageMap);
            $menuOutput .= '</li>';
        }

        if ( ! $menuOutput) {
            return '';
        }

        return '<ul>' . $menuOutput . '</ul>';
    }
}