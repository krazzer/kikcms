<?php


namespace KikCMS\Services\Website;


use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\UserService;
use Phalcon\Di\Injectable;

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

        return $pageLanguage;
    }
}