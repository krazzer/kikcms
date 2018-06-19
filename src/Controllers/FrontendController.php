<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Frontend\Extendables\TemplateVariablesBase;
use KikCMS\Classes\Translator;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\UserService;
use KikCMS\Services\Website\FrontendHelper;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * @property PageService $pageService
 * @property PageContentService $pageContentService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property Translator $translator
 * @property WebsiteService $websiteService
 * @property FrontendHelper $frontendHelper
 * @property TemplateVariablesBase $templateVariables
 * @property UserService $userService
 */
class FrontendController extends BaseController
{
    /**
     * @return ResponseInterface
     */
    public function unauthorizedAction(): ResponseInterface
    {
        $this->response->setStatusCode(401);

        return $this->response->setContent('You are not allowed to view this page');
    }

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

        if ( ! $pageLanguage || ! $pageLanguage->page || ( ! $pageLanguage->active && ! $this->userService->isLoggedIn())) {
            throw new NotFoundException();
        }

        $this->loadPage($pageLanguage);
    }

    /**
     * @param string $languageCode
     * @param int $pageId
     * @throws NotFoundException
     */
    public function pageByIdAction(string $languageCode, int $pageId)
    {
        $pageLanguage = $this->pageLanguageService->getByPageId($pageId, $languageCode);

        if ( ! $pageLanguage) {
            throw new NotFoundException($languageCode);
        }

        $this->response->redirect($this->urlService->getUrlByPageLanguage($pageLanguage, false));
    }

    /**
     * @param string $languageCode
     * @param string $pageKey
     * @throws NotFoundException
     */
    public function pageByKeyAction(string $languageCode, string $pageKey)
    {
        $pageLanguage = $this->pageLanguageService->getByPageKey($pageKey, $languageCode);

        if ( ! $pageLanguage) {
            throw new NotFoundException($languageCode);
        }

        $this->response->redirect($this->urlService->getUrlByPageLanguage($pageLanguage, false));
    }

    /**
     * @param null|string $languageCode
     * @return string
     */
    public function pageNotFoundAction(string $languageCode = null)
    {
        $this->response->setStatusCode(404);
        $this->view->reset();

        $pageLanguage = $this->pageLanguageService->getNotFoundPage($languageCode);

        if ( ! $pageLanguage) {
            return $this->translator->tl('error.404.description');
        }

        return $this->loadPage($pageLanguage);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return null|Response
     */
    private function loadPage(PageLanguage $pageLanguage): ?Response
    {
        $pageLanguageAlias = $pageLanguage;

        if ($aliasId = $pageLanguage->page->getAliasId()) {
            $pageLanguage = $this->pageLanguageService->getByPageId($aliasId, $pageLanguage->getLanguageCode());
        }

        $page         = $pageLanguage->page;
        $languageCode = $pageLanguage->language_code;
        $templateFile = $page->template;

        $this->frontendHelper->initialize($languageCode, $pageLanguage, $pageLanguageAlias);
        $this->translator->setLanguageCode($languageCode);

        $fieldVariables    = $this->pageContentService->getVariablesByPageLanguage($pageLanguage);
        $websiteVariables  = $this->templateVariables->getGlobalVariables();
        $templateVariables = $this->templateVariables->getTemplateVariables($templateFile, $fieldVariables);

        // in case a form has been send, it might want to redirect
        if ($templateVariables instanceof Response) {
            return $templateVariables;
        }

        $variables = array_merge($fieldVariables, $websiteVariables, $templateVariables);
        $variables = $this->websiteService->getForms($variables);

        // in case a form has been send, it might want to redirect
        if ($variables instanceof Response) {
            return $variables;
        }

        $this->view->languageCode = $languageCode;
        $this->view->pageLanguage = $pageLanguage;
        $this->view->page         = $page;
        $this->view->pageId       = $pageLanguage->getPageId();

        $this->view->currentUrl = $this->router->getRewriteUri();
        $this->view->baseUrl    = $this->url->getBaseUri();
        $this->view->fullUrl    = $this->url->getBaseUri() . ltrim($this->router->getRewriteUri(), '/');

        $this->view->title   = $pageLanguage->name;
        $this->view->pageKey = $page->key;
        $this->view->helper  = $this->frontendHelper;

        $this->view->setVars($variables);
        $this->view->pick('@website/templates/' . $templateFile);

        return null;
    }
}