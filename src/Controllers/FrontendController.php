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
use Psr\Log\LogLevel;

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

        return $this->frontendService->getMessageResponse($title, $description);
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
     * @param bool $existsCheck checks if the page exists in existingPageCache.
     * Set to false if the page is not expected to be in the cache, for example if a custom URL is forwarded to this function
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function pageAction(string $urlPath = null, bool $existsCheck = true): ResponseInterface
    {
        // do not use the existingPageCache if it's disabled in the config.
        if($existsCheck && ! $this->config->application->cacheExistingPages){
            $existsCheck = false;
        }

        if ($this->keyValue->get(KikCMSConfig::SETTING_MAINTENANCE) && ! $this->userService->isLoggedIn()) {
            return $this->websiteSettings->getMaintenanceResponse();
        }

        if ( ! $pageLanguage = $this->frontendService->getPageLanguageToLoadByUrlPath($urlPath, $existsCheck)) {
            throw new NotFoundException();
        }

        $this->response->setStatusCode(200);

        return $this->loadPage($pageLanguage, (string) $urlPath);
    }

    /**
     * @param string $url
     * @param int $page
     */
    public function pageIndexAction(string $url, int $page): void
    {
        $this->dispatcher->forward([
            'namespace'  => KikCMSConfig::NAMESPACE_PATH_CMS_CONTROLLERS,
            'controller' => 'frontend',
            'action'     => 'page',
            'params'     => [
                'url'         => $url,
                'existsCheck' => false,
                'page'        => $page,
            ],
        ]);
    }

    /**
     * @param string $languageCode
     * @param int $pageId
     * @throws NotFoundException
     */
    public function pageByIdAction(string $languageCode, int $pageId): void
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
    public function pageByKeyAction(string $languageCode, string $pageKey): void
    {
        $pageLanguage = $this->pageLanguageService->getByPageKey($pageKey, $languageCode);

        if ( ! $pageLanguage) {
            throw new NotFoundException($languageCode);
        }

        $this->response->redirect($this->urlService->getUrlByPageLanguage($pageLanguage));
    }

    /**
     * @param null|string $languageCode
     * @return ResponseInterface|string|null
     * @noinspection PhpVoidFunctionResultUsedInspection
     */
    public function pageNotFoundAction(string $languageCode = null): ResponseInterface|string|null
    {
        $this->response->setStatusCode(404);
        $this->view->reset();

        if ($cached404PageContent = $this->existingPageCacheService->get404PageContent()) {
            return $cached404PageContent;
        }

        if ($pageLanguage = $this->pageLanguageService->getNotFoundPage($languageCode)) {
            $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

            if ($this->config->application->pageCache) {
                if ($content = $this->pageCacheService->getContentByUrlPath($url)) {
                    return $this->response->setContent($content);
                }
            }

            $response = $this->loadPage($pageLanguage, $url);

            $this->existingPageCacheService->cache404Page($response->getContent());

            return $response;
        }

        if ($route = $this->websiteSettings->getNotFoundRoute()) {
            return $this->dispatcher->forward($route);
        }

        return $this->translator->tl('error.404.description');
    }

    /**
     * @param PageLanguage $pageLanguage
     * @param string $urlPath
     * @return null|ResponseInterface
     */
    private function loadPage(PageLanguage $pageLanguage, string $urlPath): ?ResponseInterface
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

        if (is_object($templateVariables)) {
            $templateVariables = [$templateVariables];
        }

        $variables = array_merge($langSwitchVariables, $fieldVariables, $websiteVariables, $templateVariables);

        // in case a form has been sent, it might want to redirect
        foreach ($variables as $variable) {
            if ($variable instanceof Response) {
                return $variable;
            }
        }

        $variables['languageCode'] = $languageCode;
        $variables['pageLanguage'] = $pageLanguage;
        $variables['page']         = $page;
        $variables['pageId']       = $pageLanguage->getPageId();

        $variables['currentUrl'] = $this->url->getRewriteUri();
        $variables['baseUrl']    = $this->url->getBaseUri();
        $variables['fullUrl']    = $this->url->getBaseUri() . ltrim($this->url->getRewriteUri(), '/');
        $variables['urlPath']    = $urlPath;

        $variables['title']         = $pageLanguage->name;
        $variables['pageKey']       = $page->key;
        $variables['helper']        = $this->frontendHelper;
        $variables['socialImageId'] = $this->fileService->getIdByKey(KikCMSConfig::KEY_FILE_SOCIAL);

        $response = $this->view('@website/templates/' . $templateFile, $variables);

        if ($this->config->application->pageCache) {
            if ( ! $this->pageCacheService->save($urlPath, $response->getContent())) {
                $this->logger->log(LogLevel::NOTICE, 'Writing page ' . $urlPath . ' to cache failed');
            }
        }

        return $response;
    }
}