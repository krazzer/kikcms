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
use Phalcon\Http\Response;

/**
 * @property PageService $pageService
 * @property PageContentService $pageContentService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property Translator $translator
 * @property WebsiteService $websiteService
 * @property FrontendHelper $frontendHelper
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
            return $this->translator->tl('error.404.description');
        }

        return $this->loadPage($pageLanguage);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return null|Response
     */
    private function loadPage(PageLanguage $pageLanguage)
    {
        $languageCode = $pageLanguage->language_code;
        $templateFile = $pageLanguage->page->template->file;

        $this->frontendHelper->initialize($languageCode, $pageLanguage);
        $this->translator->setLanguageCode($languageCode);

        $fieldVariables    = $this->pageContentService->getVariablesByPageLanguage($pageLanguage);
        $websiteVariables  = $this->websiteService->getWebsiteVariables();
        $templateVariables = $this->websiteService->getWebsiteTemplateVariables($templateFile);

        $variables = array_merge($fieldVariables, $websiteVariables, $templateVariables);
        $variables = $this->websiteService->getForms($variables);

        // in case a form has been send, it might want to redirect
        if ($variables instanceof Response) {
            return $variables;
        }

        $this->view->languageCode = $languageCode;
        $this->view->pageLanguage = $pageLanguage;
        $this->view->pageId       = $pageLanguage->getPageId();

        $this->view->title  = $pageLanguage->name;
        $this->view->helper = $this->frontendHelper;
        dlog(array_keys($variables));
        $this->view->setVars($variables);
        $this->view->pick('@website/templates/' . $templateFile);

        return null;
    }
}