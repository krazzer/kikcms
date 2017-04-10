<?php

namespace KikCMS\Controllers;

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
            $page = $this->urlService->getPageLanguageByUrl($url);
        } else {
            $page = $this->pageLanguageService->getDefault();
        }

        $this->view->title = $page->name;

        $this->view->pick('@website/base');
    }
}