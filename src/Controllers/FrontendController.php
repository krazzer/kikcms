<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Frontend\Extendables\TemplateVariablesBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
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
 * @property FrontendHelper $frontendHelper
 * @property PageContentService $pageContentService
 * @property PageLanguageService $pageLanguageService
 * @property PageService $pageService
 * @property TemplateVariablesBase $templateVariables
 * @property Translator $translator
 * @property UrlService $urlService
 * @property UserService $userService
 * @property WebsiteService $websiteService
 * @property WebsiteSettingsBase $websiteSettings
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
     * @return ResponseInterface
     */
    public function objectNotFoundAction(): ResponseInterface
    {
        throw new ObjectNotFoundException();
    }

    /**
     * @param string $urlPath
     * @throws NotFoundException
     */
    public function pageAction(string $urlPath = null)
    {
        if ($urlPath && $urlPath !== '/') {
            $pageLanguage = $this->urlService->getPageLanguageByUrlPath($urlPath);
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

        $this->response->redirect($this->urlService->getUrlByPageLanguage($pageLanguage));
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

        $this->response->redirect($this->urlService->getUrlByPageLanguage($pageLanguage));
    }

    /**
     * @param null|string $languageCode
     * @return string
     */
    public function pageNotFoundAction(string $languageCode = null)
    {
        $this->response->setStatusCode(404);
        $this->view->reset();

        if ($pageLanguage = $this->pageLanguageService->getNotFoundPage($languageCode)) {
            return $this->loadPage($pageLanguage);
        }

        if ($route = $this->websiteSettings->getNotFoundRoute()) {
            return $this->dispatcher->forward($route);
        }

        return $this->translator->tl('error.404.description');
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
        $templateVariables = $this->templateVariables->getTemplateVariables($templateFile);

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