<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Translator;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Website\FrontendHelper;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\Website\WebsiteService;

/**
 * @property PageService $pageService
 * @property PageContentService $pageContentService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property Translator $translator
 * @property WebsiteService $websiteService
 */
class FrontendController extends BaseController
{
    /**
     * @param string $url
     * @throws NotFoundException
     */
    public function pageAction(string $url = null)
    {
        if ($url) {
            $pageLanguage = $this->urlService->getPageLanguageByUrl($url);
        } else {
            $pageLanguage = $this->pageLanguageService->getDefault();
        }

        if ( ! $pageLanguage) {
            throw new NotFoundException("Page not found");
        }

        $this->loadPage($pageLanguage);
    }

    /**
     * @return string
     */
    public function pageNotFoundAction()
    {
        $this->view->reset();

        $pageLanguage = $this->pageLanguageService->getNotFoundPage();

        if ( ! $pageLanguage) {
            return $this->translator->tlb('pageNotFound');
        }

        return $this->loadPage($pageLanguage);
    }

    /**
     * @param string $templateFile
     * @return array
     */
    private function getWebsiteTemplateVariables(string $templateFile): array
    {
        $methodName = 'get' . ucfirst($templateFile) . 'Variables';

        return $this->websiteService->callMethod('TemplateVariables', $methodName, [], false, []);
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    private function loadPage(PageLanguage $pageLanguage)
    {
        $languageCode   = $pageLanguage->language_code;
        $frontendHelper = new FrontendHelper($languageCode);
        $variables      = $this->pageContentService->getVariablesByPageLanguage($pageLanguage);
        $templateFile   = $pageLanguage->page->template->file;

        $this->translator->setLanguageCode($languageCode);

        $this->view->title        = $pageLanguage->name;
        $this->view->languageCode = $languageCode;
        $this->view->helper       = $frontendHelper;

        $this->view->setVars($variables);
        $this->view->setVars($this->getWebsiteTemplateVariables($templateFile), true);
        $this->view->pick('@website/templates/' . $templateFile);
    }
}