<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\PageNotFoundException;
use KikCMS\Classes\Translator;
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
     * @throws PageNotFoundException
     */
    public function pageAction(string $url = null)
    {
        if ($url) {
            $pageLanguage = $this->urlService->getPageLanguageByUrl($url);
        } else {
            $pageLanguage = $this->pageLanguageService->getDefault();
        }

        if( ! $pageLanguage){
            throw new PageNotFoundException("Page not found");
        }

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
}