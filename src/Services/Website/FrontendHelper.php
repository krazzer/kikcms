<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Classes\Translator;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\PageLanguage;
use KikCMS\ObjectLists\FullPageMap;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\FullPageService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Cache\Backend;
use Phalcon\Di\Injectable;

/**
 * @property UrlService $urlService
 * @property PageService $pageService
 * @property PageLanguageService $pageLanguageService
 * @property CacheService $cacheService
 * @property Backend $cache
 * @property Translator $translator
 * @property FullPageService $fullPageService
 */
class FrontendHelper extends Injectable
{
    /** @var string */
    private $languageCode;

    /** @var PageLanguage */
    private $currentPageLanguage;

    /** @var PageLanguageMap */
    private $currentPathCached;

    /**
     * @param string $languageCode
     * @param PageLanguage $pageLanguage
     */
    public function initialize(string $languageCode, PageLanguage $pageLanguage)
    {
        $this->languageCode        = $languageCode;
        $this->currentPageLanguage = $pageLanguage;
    }

    /**
     * @param string $type
     * @param int $imageId
     * @return string
     */
    public function bgThumb(string $type, int $imageId)
    {
        return "background-image: url('/finder/thumb/" . $type . "/" . $imageId . "')";
    }

    /**
     * Build a multi-level ul li structured menu
     *
     * @param int $menuId
     * @param int|null $maxLevel
     * @param string|null $template
     * @param int|null $restrictTemplateId
     * @param bool $cache
     * @return string
     */
    public function menu(int $menuId, int $maxLevel = null, string $template = null, int $restrictTemplateId = null, $cache = true): string
    {
        $cacheKey = $this->cacheService->createKey(CacheConfig::MENU, $menuId, $this->languageCode, $maxLevel, $template, $restrictTemplateId);

        if($cache && $menu = $this->cache->get($cacheKey)){
            return $this->setActive($menu);
        }

        $fullPageMap = $this->fullPageService->getByMenuId($menuId, $this->languageCode, $maxLevel, $restrictTemplateId);
        $menu = $this->buildMenu($menuId, $maxLevel, $template, $fullPageMap);

        if($cache){
            $this->cache->save($cacheKey, $menu, CacheConfig::ONE_DAY);
        }

        return $this->setActive($menu);
    }

    /**
     * @return PageLanguage
     */
    public function getCurrentPageLanguage(): PageLanguage
    {
        return $this->currentPageLanguage;
    }

    /**
     * Get a map with PageLanguages walking downwards the page hierarchy
     *
     * @return PageLanguageMap
     */
    public function getPath(): PageLanguageMap
    {
        if ($this->currentPathCached) {
            return $this->currentPathCached;
        }

        $this->currentPathCached = $this->pageLanguageService->getPath($this->currentPageLanguage);

        return $this->currentPathCached;
    }

    /**
     * @param int $pageId
     * @return string
     */
    public function getUrl(int $pageId): string
    {
        $langCode = $this->translator->getLanguageCode();

        return $this->urlService->getUrlByPageId($pageId, $langCode);
    }

    /**
     * @param int $pageId
     * @return bool
     */
    public function inPath(int $pageId): bool
    {
        return $this->getPath()->has($pageId);
    }

    /**
     * @param int $parentId
     * @param int|null $maxLevel
     * @param string|null $template
     * @param FullPageMap $fullPageMap
     * @param int|null $initialLevel
     *
     * @return string
     */
    private function buildMenu(int $parentId, int $maxLevel = null, string $template = null, FullPageMap $fullPageMap, int $initialLevel = null): string
    {
        if($fullPageMap->isEmpty()){
            return '';
        }

        $initialLevel = $initialLevel ?: $fullPageMap->getFirst()->getLevel() - 1;

        $menuOutput = '';

        /** @var FullPage $fullPage */
        foreach ($fullPageMap as $pageId => $fullPage) {
            if ($fullPage->getParentId() != $parentId) {
                continue;
            }

            if ($maxLevel !== null && (int) $fullPage->getLevel() >= (int) $initialLevel + $maxLevel) {
                $subMenuOutput = '';
            } else {
                $subMenuOutput = $this->buildMenu($pageId, $maxLevel, $template, clone $fullPageMap, $initialLevel);
            }

            $menuOutput .= '<li class="s' . $pageId . '" data-id="' . $pageId . '">';
            $menuOutput .= $this->getMenuItemOutput($fullPage, $template);
            $menuOutput .= $subMenuOutput;
            $menuOutput .= '</li>';
        }

        if ( ! $menuOutput) {
            return '';
        }

        return '<ul>' . $menuOutput . '</ul>';
    }

    /**
     * @param FullPage $fullPage
     * @param string|null $template
     *
     * @return string
     */
    private function getMenuItemOutput(FullPage $fullPage, string $template = null): string
    {
        if ($template) {
            return $this->view->getPartial('@kikcms/frontend/menu', [
                'menuBlock' => 'menu' . ucfirst($template),
                'page'      => $fullPage,
            ]);
        }

        return '<a href="' . $fullPage->getUrl() . '">' . $fullPage->getName() . '</a>';
    }

    /**
     * Replaces placeholder li class if they are currently visited
     *
     * @param string $menu
     * @return string
     */
    private function setActive(string $menu): string
    {
        $path = $this->getPath();

        foreach ($path as $pageId => $pageLanguage){
            $menu = str_replace('class="s' . $pageId . '"', 'class="selected"', $menu);
        }

        return $menu;
    }
}