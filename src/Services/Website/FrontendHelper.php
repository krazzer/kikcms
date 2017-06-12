<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Classes\Frontend\Menu;
use KikCMS\Classes\Translator;
use KikCMS\Models\PageLanguage;
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
 * @property MenuService $menuService
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
     * @param int|null $templateId
     * @param bool $cache
     * @return string
     */
    public function menu(int $menuId, int $maxLevel = null, string $template = null, int $templateId = null, $cache = true): string
    {
        $menu = (new Menu())
            ->setMenuId($menuId)
            ->setMaxLevel($maxLevel)
            ->setTemplate($template)
            ->setRestrictTemplateId($templateId)
            ->setLanguageCode($this->languageCode)
            ->setCache($cache);

        return $this->getMenuOutput($menu);
    }

    /**
     * @param Menu $menu
     * @return string
     */
    public function getMenuOutput(Menu $menu): string
    {
        $getMenu = function () use ($menu) {
            $this->menuService->addFullPageMap($menu);
            return $this->buildMenu($menu->getMenuId(), $menu);
        };

        if ( ! $menu->isCache()) {
            return $this->setActive($getMenu());
        }

        $cacheKey = $this->menuService->getCacheKey($menu);

        return $this->setActive($this->cacheService->cache($cacheKey, $getMenu));
    }

    /**
     * @return PageLanguage
     */
    public function getCurrentPageLanguage(): PageLanguage
    {
        return $this->currentPageLanguage;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
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
     * @param int|mixed|string $pageId
     * @return string
     */
    public function getUrl($pageId): string
    {
        $langCode = $this->translator->getLanguageCode();

        if(is_numeric($pageId)){
            return $this->urlService->getUrlByPageId($pageId, $langCode);
        } else {
            return $this->urlService->getUrlByPageKey($pageId, $langCode);
        }
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
     * @param Menu $menu
     * @return string
     */
    private function buildMenu(int $parentId, Menu $menu): string
    {
        $fullPageMap  = clone $menu->getFullPageMap();
        $initialLevel = $fullPageMap->isEmpty() ? 0 : $menu->getFullPageMap()->getFirst()->getLevel() - 1;

        $menuOutput = '';

        /** @var FullPage $fullPage */
        foreach ($fullPageMap as $pageId => $fullPage) {
            if ($fullPage->getParentId() != $parentId) {
                continue;
            }

            if ($menu->getMaxLevel() !== null && (int) $fullPage->getLevel() >= (int) $initialLevel + $menu->getMaxLevel()) {
                $subMenuOutput = '';
            } else {
                $subMenuOutput = $this->buildMenu($pageId, $menu);
            }

            $menuOutput .= '<li class="s' . $pageId . '" data-id="' . $pageId . '">';
            $menuOutput .= $this->getMenuItemOutput($fullPage, $menu->getTemplate());
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

        foreach ($path as $pageId => $pageLanguage) {
            $menu = str_replace('class="s' . $pageId . '"', 'class="selected"', $menu);
        }

        return $menu;
    }
}