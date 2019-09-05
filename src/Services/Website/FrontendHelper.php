<?php declare(strict_types=1);

namespace KikCMS\Services\Website;


use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Classes\Frontend\Menu;
use KikCMS\Classes\Translator;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Page;
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

    /** @var PageLanguage is the same as currentPageLanguage if the page is not an alias */
    private $currentPageLanguageAlias;

    /** @var PageLanguageMap */
    private $currentPathCached;

    /** @var string|null */
    private $aliasUrl;

    public function __construct()
    {
        $this->languageCode = $this->translator->getLanguageCode();
    }

    /**
     * @return null|string
     */
    public function getAliasUrl()
    {
        return $this->aliasUrl;
    }

    /**
     * @param string $languageCode
     * @param PageLanguage $pageLanguage
     * @param PageLanguage $pageLanguageAlias
     */
    public function initialize(string $languageCode, PageLanguage $pageLanguage, PageLanguage $pageLanguageAlias)
    {
        $this->languageCode             = $languageCode;
        $this->currentPageLanguage      = $pageLanguage;
        $this->currentPageLanguageAlias = $pageLanguageAlias;

        if ($pageLanguage->getPageId() !== $pageLanguageAlias->getPageId()) {
            $this->aliasUrl = $this->urlService->getUrlByPageLanguage($pageLanguage);
        }
    }

    /**
     * Build a multi-level ul li structured menu
     *
     * @param int|string $menuKeyOrId can be either the id or the key of the menu
     * @param int|null $maxLevel
     * @param string|null $template
     * @param null|string $templateKey
     * @param bool $cache
     * @return string
     */
    public function menu($menuKeyOrId, int $maxLevel = null, string $template = null, string $templateKey = null, $cache = true): string
    {
        if ( ! $menuKeyOrId) {
            return '';
        }

        if( ! $menuId = $this->pageService->getIdByKeyOrId($menuKeyOrId)){
            return '';
        }

        // disable cache on dev
        if ($this->config->application->env === KikCMSConfig::ENV_DEV) {
            $cache = false;
        }

        $menu = (new Menu())
            ->setMenuId($menuId)
            ->setMaxLevel($maxLevel)
            ->setTemplate($template)
            ->setRestrictTemplate($templateKey)
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

        return $this->setActive((string) $this->cacheService->cache($cacheKey, $getMenu));
    }

    /**
     * @return PageLanguage
     */
    public function getCurrentPageLanguage(): PageLanguage
    {
        return $this->currentPageLanguage;
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->currentPageLanguage->page;
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

        if ( ! $this->currentPageLanguage) {
            return new PageLanguageMap();
        }

        $this->currentPathCached = $this->pageLanguageService->getPath($this->currentPageLanguage, $this->currentPageLanguageAlias);

        return $this->currentPathCached;
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

        foreach ($fullPageMap as $pageId => $fullPage) {
            if ($fullPage->getParentId() != $parentId) {
                continue;
            }

            $menuOutput .= $this->buildMenuItem($menu, $fullPage, $initialLevel);
        }

        if ( ! $menuOutput) {
            return '';
        }

        return '<ul>' . $menuOutput . '</ul>';
    }

    /**
     * @param Menu $menu
     * @param FullPage $fullPage
     * @param int $initialLevel
     * @return string
     */
    private function buildMenuItem(Menu $menu, FullPage $fullPage, int $initialLevel): string
    {
        $pageId = $fullPage->getPageId();

        if ($menu->getMaxLevel() !== null && (int) $fullPage->getLevel() >= (int) $initialLevel + $menu->getMaxLevel()) {
            $subMenuOutput = '';
        } else {
            $subMenuOutput = $this->buildMenu($pageId, $menu);
        }

        $relativeLevel = $fullPage->getLevel() - $initialLevel;
        $pageKey       = $fullPage->getPage()->key;
        $pageKeyAttr   = $pageKey ? ' data-key="' . $pageKey . '"' : '';

        $output = '<li class="s' . $pageId . '" data-id="' . $pageId . '"' . $pageKeyAttr . '>';
        $output .= $this->getMenuItemOutput($fullPage, $menu->getTemplate(), $relativeLevel);
        $output .= $subMenuOutput;
        $output .= '</li>';

        return $output;
    }

    /**
     * @param FullPage $fullPage
     * @param string|null $template
     * @param int $relativeLevel
     * @return string
     */
    private function getMenuItemOutput(FullPage $fullPage, string $template = null, int $relativeLevel): string
    {
        if ($template) {
            return $this->view->getPartial('@kikcms/frontend/menu', [
                'menuBlock'     => 'menu' . ucfirst($template),
                'page'          => $fullPage,
                'relativeLevel' => $relativeLevel,
            ]);
        }

        return '<a title="' . $fullPage->getName() . '" href="' . $fullPage->getUrl() . '">' . $fullPage->getName() . '</a>';
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