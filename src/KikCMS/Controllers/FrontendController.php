<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Translator;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Frontend\MenuBuilder;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;

/**
 * @property PageService $pageService
 * @property PageContentService $pageContentService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property Translator $translator
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

        if( ! $pageLanguage){
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

        if( ! $pageLanguage){
            return $this->translator->tl('pageNotFound');
        }

        return $this->loadPage($pageLanguage);
    }

    /**
     * @param string $templateFile
     * @return array
     */
    private function getWebsiteTemplateVariables(string $templateFile): array
    {
        $templateVariablesClass = 'Website\Classes\TemplateVariables';

        if( ! class_exists($templateVariablesClass)){
            return [];
        }

        $templateVariables = new $templateVariablesClass();

        $methodName = 'get' . ucfirst($templateFile) . 'Variables';

        if( ! method_exists($templateVariables, $methodName)){
            return [];
        }

        return $templateVariables->$methodName();
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    private function loadPage(PageLanguage $pageLanguage)
    {
        $languageCode = $pageLanguage->language_code;
        $menuBuilder  = new MenuBuilder($languageCode);
        $variables    = $this->pageContentService->getVariablesByPageLanguage($pageLanguage);
        $templateFile = $pageLanguage->page->template->file;

        $this->translator->setLanguageCode($languageCode);

        $this->view->title        = $pageLanguage->name;
        $this->view->languageCode = $languageCode;
        $this->view->menuBuilder  = $menuBuilder;

        $this->view->setVars($variables);
        $this->view->setVars($this->getWebsiteTemplateVariables($templateFile), true);
        $this->view->pick('@website/templates/' . $templateFile);
    }
}