<?php


namespace KikCMS\Services\Website;


use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\UserService;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property UserService $userService
 */
class FrontendService extends Injectable
{
    /**
     * @param string|null $urlPath
     * @return PageLanguage|null
     */
    public function getPageLanguageToLoadByUrlPath(?string $urlPath): ?PageLanguage
    {
        if ($urlPath && $urlPath !== '/') {
            $pageLanguage = $this->urlService->getPageLanguageByUrlPath($urlPath);
        } else {
            $pageLanguage = $this->pageLanguageService->getDefault();
        }

        if ( ! $pageLanguage || ! $pageLanguage->page || ( ! $pageLanguage->active && ! $this->userService->isLoggedIn())) {
            return null;
        }

        if($pageLanguage->page->type === Page::TYPE_MENU){
            return null;
        }

        return $pageLanguage;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return array
     */
    public function getLangSwitchVariables(PageLanguage $pageLanguage): array
    {
        $pageId   = $pageLanguage->getPageId();
        $cacheKey = CacheConfig::getOtherLangMapKey($pageLanguage);

        return $this->cacheService->cache($cacheKey, function () use ($pageId, $pageLanguage){
            $urlMap = $this->getUrlMapByPageId($pageId);

            $return = ['langUrlMap' => $urlMap];

            if (count($urlMap) == 2) {
                $otherLangMap = $urlMap;
                unset($otherLangMap[$pageLanguage->getLanguageCode()]);

                $return['otherLangCode'] = first_key($otherLangMap);
                $return['otherLangUrl']  = first($otherLangMap);
            }

            return $return;
        });
    }

    /**
     * @param int $pageId
     * @return array
     */
    private function getUrlMapByPageId(int $pageId): array
    {
        $activeLanguages = $this->languageService->getLanguages(true);

        if ($activeLanguages->isEmpty()) {
            return [];
        }

        $urlMap = [];

        $pageLanguageMap = $this->pageLanguageService->getAllByPageId($pageId);

        foreach ($activeLanguages as $code => $language) {
            if ($pageLanguage = $pageLanguageMap->get($code)) {
                $urlMap[$code] = $this->urlService->getUrlByPageLanguage($pageLanguage);
            } else {
                $urlMap[$code] = $this->urlService->getUrlByPageKey(KikCMSConfig::KEY_PAGE_DEFAULT, $code);
            }
        }

        return $urlMap;
    }
}