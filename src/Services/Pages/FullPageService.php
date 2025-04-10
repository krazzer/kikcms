<?php declare(strict_types=1);

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Classes\Frontend\Menu;
use KikCMS\Classes\Translator;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\FullPageMap;
use KikCMS\ObjectLists\PageMap;
use KikCMS\Services\LanguageService;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * @property PageService $pageService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property LanguageService $languageService
 * @property Translator $translator
 */
class FullPageService extends Injectable
{
    /**
     * @param Menu $menu
     * @return FullPageMap
     */
    public function getMapByMenu(Menu $menu): FullPageMap
    {
        $pageMap = $this->pageService->getOffspringByMenu($menu);
        return $this->getByPageMap($pageMap, $menu->getLanguageCode());
    }

    /**
     * @param PageMap $pageMap
     * @param string|null $langCode
     * @param bool $activeOnly
     * @return FullPageMap
     */
    public function getByPageMap(PageMap $pageMap, string $langCode = null, bool $activeOnly = true): FullPageMap
    {
        $langCode    = $langCode ?: $this->translator->getLanguageCode();
        $fullPageMap = new FullPageMap();

        $pageLangMap    = $this->pageLanguageService->getByPageMap($pageMap, $langCode, $activeOnly);
        $pageFieldTable = $this->pageLanguageService->getPageFieldTable($pageMap, $langCode);

        foreach ($pageMap as $page) {
            if ($pageLang = $pageLangMap->get($page->getRealId())) {
                $content = $pageFieldTable[$page->getRealId()] ?? [];
                $url     = $this->urlService->getUrlByPageLanguage($pageLang, $page);

                $fullPageMap->add(new FullPage($page, $pageLang, $content, $url), $page->getId());
            }
        }

        return $fullPageMap;
    }

    /**
     * @param Page $page
     * @param string|null $langCode
     * @return FullPage|null
     */
    public function getByPage(Page $page, string $langCode = null): ?FullPage
    {
        $pageMap = (new PageMap)->add($page, $page->getId());

        $fullPageMap = $this->getByPageMap($pageMap, $langCode, false);

        if ($fullPageMap->isEmpty()) {
            return null;
        }

        return $fullPageMap->getFirst();
    }

    /**
     * @param string $template
     * @param callable|null $queryModify
     * @return FullPageMap
     */
    public function getByTemplate(string $template, callable $queryModify = null): FullPageMap
    {
        return $this->getByPageMap($this->pageService->getByTemplate($template, $queryModify));
    }

    /**
     * @param string $pageKey
     * @param callable|null $queryModifier
     * @return FullPageMap
     */
    public function getMapByParentKey(string $pageKey, callable $queryModifier = null): FullPageMap
    {
        if( ! $parentPage = $this->pageService->getByKey($pageKey)){
            return new FullPageMap;
        }

        return $this->getMapByParentPage($parentPage, $queryModifier);
    }

    /**
     * @param Page $parentPage
     * @param callable|null $queryModifier
     * @return FullPageMap
     */
    public function getMapByParentPage(Page $parentPage, callable $queryModifier = null): FullPageMap
    {
        $childPageMap = $this->pageService->getChildren($parentPage, $queryModifier);

        return $this->getByPageMap($childPageMap);
    }
}