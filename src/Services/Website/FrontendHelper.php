<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Classes\Translator;
use KikCMS\Models\PageLanguage;
use KikCMS\ObjectLists\FullPageMap;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\FullPageService;
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
     * @return string
     */
    public function menu(int $menuId, int $maxLevel = null, string $template = null): string
    {
        $fullPageMap = $this->fullPageService->getByMenuId($menuId, $this->languageCode, $maxLevel);

        return $this->buildMenu($menuId, $maxLevel, $template, $fullPageMap);
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
        $initialLevel = $initialLevel ?: $fullPageMap->getFirst()->getLevel() - 1;

        $menuOutput = '';

        /** @var FullPage $fullPage */
        foreach ($fullPageMap as $fullPage) {
            if ($fullPage->getParentId() != $parentId) {
                continue;
            }

            if ($maxLevel !== null && (int) $fullPage->getLevel() >= (int) $initialLevel + $maxLevel) {
                $subMenuOutput = '';
            } else {
                $subMenuOutput = $this->buildMenu($fullPage->getId(), $maxLevel, $template, $fullPageMap, $initialLevel);
            }

            $class = $this->inPath($fullPage->getId()) ? 'selected' : '';

            $menuOutput .= '<li class="' . $class . '">';
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
            dlog($fullPage->getContent());
            return $this->view->getPartial('@kikcms/frontend/menu', [
                'menuBlock' => 'menu' . ucfirst($template),
                'page'      => $fullPage,
            ]);
        }

        return '<a href="' . $fullPage->getUrl() . '">' . $fullPage->getName() . '</a>';
    }
}