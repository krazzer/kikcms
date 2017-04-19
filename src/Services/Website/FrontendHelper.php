<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\Translator;
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
 * @property Translator $translator
 */
class FrontendHelper extends Injectable
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
    public function menu(int $menuId): string
    {
        $cacheKey = CacheConfig::MENU . ':' . $menuId . $this->languageCode;

        return $this->cacheService->cache($cacheKey, function() use ($menuId){
            if ( ! $menu = Page::getById($menuId)){
                return '';
            }

            $pageMap         = $this->pageService->getChildren($menu);
            $pageLanguageMap = $this->pageLanguageService->getByPageMap($pageMap, $this->languageCode);

            return $this->buildMenu($menu, $pageMap, $pageLanguageMap);
        });
    }

    /**
     * @param string $string
     * @param array $replaces
     * @param int|null $groupId
     * @return mixed|string
     */
    public function tl($string, $replaces = [], int $groupId = null)
    {
        return $this->translator->tlf($string, $replaces, $groupId);
    }

    /**
     * @param int $pageId
     * @return string
     */
    public function getUrl(int $pageId): string
    {
        return $this->urlService->getUrlByPageId($pageId);
    }

    /**
     * @param Page $parentPage
     * @param Page[] $pageMap
     * @param PageLanguage[] $pageLanguageMap
     * @return string
     */
    private function buildMenu(Page $parentPage, array $pageMap, array $pageLanguageMap): string
    {
        $menuOutput = '';

        /** @var Page $page */
        foreach ($pageMap as $page) {
            if ($page->parent_id != $parentPage->getId()) {
                continue;
            }

            if( ! array_key_exists($page->getId(), $pageLanguageMap)){
                continue;
            }

            $pageLanguage = $pageLanguageMap[$page->getId()];

            $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

            $menuOutput .= '<li><a href="' . $url . '">' . $pageLanguage->name . '</a>';
            $menuOutput .= $this->buildMenu($page, $pageMap, $pageLanguageMap);
            $menuOutput .= '</li>';
        }

        if ( ! $menuOutput) {
            return '';
        }

        return '<ul>' . $menuOutput . '</ul>';
    }
}