<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Classes\Frontend\Menu;
use KikCMS\Classes\Translator;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\FullPageMap;
use KikCMS\ObjectLists\PageMap;
use KikCMS\Services\LanguageService;
use Phalcon\Di\Injectable;

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
     * @return FullPageMap
     */
    public function getByPageMap(PageMap $pageMap, string $langCode = null): FullPageMap
    {
        $langCode = $langCode ?: $this->translator->getLanguageCode();

        $fullPageMap = new FullPageMap();

        $pageLangMap    = $this->pageLanguageService->getByPageMap($pageMap, $langCode);
        $pageFieldTable = $this->pageLanguageService->getPageFieldTable($pageMap, $langCode);

        foreach ($pageMap as $pageId => $page) {
            if ( ! $pageLangMap->has($pageId)) {
                continue;
            }

            if (array_key_exists($pageId, $pageFieldTable)) {
                $content = $pageFieldTable[$pageId];
            } else {
                $content = [];
            }

            $pageLang = $pageLangMap->get($pageId);

            $url = $this->urlService->getUrlByPageLanguage($pageLang);

            $fullPageMap->add(new FullPage($page, $pageLang, $content, $url));
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

        $fullPageMap = $this->getByPageMap($pageMap, $langCode);

        if($fullPageMap->isEmpty()){
            return null;
        }

        return $fullPageMap->getFirst();
    }
}