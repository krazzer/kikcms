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
use Phalcon\Http\ResponseInterface;

/**
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property UserService $userService
 */
class FrontendService extends Injectable
{
    /**
     * Return a response with a message. This can be used for error or maintainance messages, where the website is
     * not functional
     *
     * @param string $title
     * @param string $description
     * @return ResponseInterface
     */
    public function getMessageResponse(string $title, string $description): ResponseInterface
    {
        return $this->response->setContent($this->view->getPartial('@kikcms/frontend/message', [
            'title'       => $title,
            'description' => $description,
            'customCss'   => $this->websiteSettings->getCustomCss(),
        ]));
    }

    /**
     * @param string|null $urlPath
     * @param bool $existsCheck
     * @return PageLanguage|null
     */
    public function getPageLanguageToLoadByUrlPath(?string $urlPath, bool $existsCheck = true): ?PageLanguage
    {
        if($existsCheck && $this->existingPageCacheService->exists($urlPath) === false){
            return null;
        }

        if ($urlPath && $urlPath !== '/') {
            $pageLanguage = $this->urlService->getPageLanguageByUrlPath($urlPath);
        } else {
            $pageLanguage = $this->pageLanguageService->getDefault();
        }

        if ( ! $pageLanguage && $existsCheck) {
            $this->existingPageCacheService->buildCache();
        }

        if ( ! $pageLanguage || ! $pageLanguage->page || ( ! $pageLanguage->active && ! $this->userService->isLoggedIn())) {
            return null;
        }

        if($pageLanguage->page->getType() === Page::TYPE_MENU){
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