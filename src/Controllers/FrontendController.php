<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Frontend\Extendables\TemplateVariablesBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Translator;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\StatusCodes;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\UserService;
use KikCMS\Services\Website\FrontendHelper;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\Website\FrontendService;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * @property FrontendHelper $frontendHelper
 * @property FrontendService $frontendService
 * @property PageContentService $pageContentService
 * @property PageLanguageService $pageLanguageService
 * @property PageService $pageService
 * @property TemplateVariablesBase $templateVariables
 * @property Translator $translator
 * @property UrlService $urlService
 * @property UserService $userService
 * @property WebsiteSettingsBase $websiteSettings
 */
class FrontendController extends BaseController
{
    /**
     * @return ResponseInterface
     */
    public function resourcesExceededAction(): ResponseInterface
    {
        $this->response->setStatusCode(StatusCodes::SERVICE_UNAVAILABLE);
        return $this->response->setContent(StatusCodes::SERVICE_UNAVAILABLE_MESSAGE);
    }

    /**
     * @return ResponseInterface
     */
    public function databaseConnectionFailureAction(): ResponseInterface
    {
        $title       = $this->translator->tl('error.database.title');
        $description = $this->translator->tl('error.database.description');

        return $this->message($title, $description);
    }

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
     * @param string|null $urlPath
     * @return Response|ResponseInterface|void
     * @throws NotFoundException
     */
    public function pageAction(string $urlPath = null)
    {
        if ($this->keyValue->get(KikCMSConfig::SETTING_MAINTENANCE) && ! $this->userService->isLoggedIn()) {
            $title       = $this->translator->tl('maintenance.title');
            $description = $this->translator->tl('maintenance.description');

            return $this->message($title, $description);
        }

        if ( ! $pageLanguage = $this->frontendService->getPageLanguageToLoadByUrlPath($urlPath)) {
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
     * @noinspection PhpVoidFunctionResultUsedInspection
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
        if ($aliasId = $pageLanguage->page->getAliasId()) {
            $pageLanguage = $this->pageLanguageService->getByPageId($aliasId, $pageLanguage->getLanguageCode());
        }

        $page         = $pageLanguage->page;
        $languageCode = $pageLanguage->getLanguageCode();
        $templateFile = $page->getTemplate();

        $this->frontendHelper->initialize($languageCode, $pageLanguage);
        $this->translator->setLanguageCode($languageCode);

        $langSwitchVariables = $this->frontendService->getLangSwitchVariables($pageLanguage);
        $fieldVariables      = $this->pageContentService->getVariablesByPageLanguage($pageLanguage);
        $websiteVariables    = $this->templateVariables->getGlobalVariables();
        $templateVariables   = $this->templateVariables->getTemplateVariables($templateFile);

        // in case a form has been send, it might want to redirect
        if ($templateVariables instanceof Response) {
            return $templateVariables;
        }

        $variables = array_merge($langSwitchVariables, $fieldVariables, $websiteVariables, $templateVariables);

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

    /**
     * @param string $title
     * @param string $description
     * @return ResponseInterface
     */
    private function message(string $title, string $description): ResponseInterface
    {
        return $this->response->setContent($this->view->getPartial('@kikcms/frontend/message', [
            'title'       => $title,
            'description' => $description,
            'customCss'   => $this->websiteSettings->getCustomCss(),
        ]));
    }
}