<?php

namespace KikCMS\Controllers;

use KikCMS\Services\Frontend\MenuBuilder;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;

/**
 * @property PageService $pageService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 */
class FrontendController extends BaseController
{
    /**
     * @param string $url
     */
    public function pageAction(string $url = null)
    {
        if ($url) {
            $pageLanguage = $this->urlService->getPageLanguageByUrl($url);
        } else {
            $pageLanguage = $this->pageLanguageService->getDefault();
        }

        $menuBuilder = new MenuBuilder($pageLanguage->language_code);

        $this->view->title        = $pageLanguage->name;
        $this->view->languageCode = $pageLanguage->language_code;
        $this->view->menuBuilder  = $menuBuilder;

        $this->view->pick('@website/base');
    }
}