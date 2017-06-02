<?php

namespace KikCMS\Services\Website;


use KikCMS\Classes\Translator;
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

    /** @var PageLanguage */
    private $currentPageLanguage;

    /** @var PageLanguage[] mapped by pageId */
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
     * @param array $fields
     * @return string
     */
    public function menu(int $menuId, int $maxLevel = null, string $template = null, array $fields = []): string
    {
        if ( ! $menu = Page::getById($menuId)) {
            return '';
        }

        $pageMap         = $this->pageService->getChildren($menu, $maxLevel);
        $pageLanguageMap = $this->pageLanguageService->getByPageMap($pageMap, $this->languageCode);

        if ($fields) {
            $pageFieldTable = $this->pageLanguageService->getPageFieldTable($pageMap, $this->languageCode, $fields);
        } else {
            $pageFieldTable = [];
        }

        return $this->buildMenu($menu, $pageMap, $pageLanguageMap, $maxLevel, (int) $menu->level, $template, $pageFieldTable);
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
     * @return PageLanguage[]
     */
    public function getPath(): array
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
        return array_key_exists($pageId, $this->getPath());
    }

    /**
     * @param Page $parentPage
     * @param Page[] $pageMap
     * @param PageLanguage[] $pageLanguageMap
     * @param int|null $maxLevel
     * @param int $initialLevel
     * @param string|null $template
     * @param array $pageFieldTable
     * @return string
     */
    private function buildMenu(Page $parentPage, array $pageMap, array $pageLanguageMap, int $maxLevel = null, int $initialLevel, string $template = null, array $pageFieldTable): string
    {
        $menuOutput = '';

        /** @var Page $page */
        foreach ($pageMap as $page) {
            if ($page->parent_id != $parentPage->getId()) {
                continue;
            }

            if ( ! array_key_exists($page->getId(), $pageLanguageMap)) {
                continue;
            }

            $pageLanguage = $pageLanguageMap[$page->getId()];

            if ($maxLevel !== null && (int) $page->level >= (int) $initialLevel + $maxLevel) {
                $subMenuOutput = '';
            } else {
                $subMenuOutput = $this->buildMenu($page, $pageMap, $pageLanguageMap, $maxLevel, $initialLevel, $template, $pageFieldTable);
            }

            $class = $this->inPath($page->getId()) ? 'selected' : '';

            if (array_key_exists($page->getId(), $pageFieldTable)) {
                $params = $pageFieldTable[$page->getId()];
            } else {
                $params = [];
            }

            $menuOutput .= '<li class="' . $class . '">';
            $menuOutput .= $this->getMenuItemOutput($pageLanguage, $template, $params);
            $menuOutput .= $subMenuOutput;
            $menuOutput .= '</li>';
        }

        if ( ! $menuOutput) {
            return '';
        }

        return '<ul>' . $menuOutput . '</ul>';
    }

    /**
     * @param PageLanguage $pageLanguage
     * @param string|null $template
     * @param array $params
     * @return string
     */
    private function getMenuItemOutput(PageLanguage $pageLanguage, string $template = null, array $params = []): string
    {
        if ($template) {
            return $this->view->getPartial('@kikcms/frontend/menu', array_merge($params, [
                'menuBlock'    => 'menu' . ucfirst($template),
                'id'           => $pageLanguage->getPageId(),
                'name'         => $pageLanguage->name,
                'pageLanguage' => $pageLanguage,
                'url'          => $this->getUrl($pageLanguage->getPageId()),
            ]));
        }

        $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

        return '<a href="' . $url . '">' . $pageLanguage->name . '</a>';
    }
}