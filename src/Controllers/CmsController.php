<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use DateTime;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Users;
use KikCMS\Forms\SettingsForm;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\DataTable\TinyMceService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\TranslationService;
use KikCMS\Services\UserService;
use KikCMS\Services\Util\DateTimeService;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * @property AccessControl $acl
 * @property AnalyticsService $analyticsService
 * @property DateTimeService $dateTimeService
 * @property CmsService $cmsService
 * @property LanguageService $languageService
 * @property TinyMceService $tinyMceService
 * @property TranslationService $translationService
 * @property Translator $translator
 * @property UrlService $urlService
 * @property UserService $userService
 */
class CmsController extends BaseCmsController
{
    /**
     * Renders a file picker used for TinyMCE
     */
    public function filePickerAction()
    {
        $finder = new Finder();
        $finder->setPickingMode(true);

        $this->view->title  = $this->translator->tl('menu.item.media');
        $this->view->finder = $finder->render();
        $this->view->pick('cms/filePicker');
    }

    /**
     * First page to show when the user logs in, to avoid POST reset, redirect.
     *
     * @return Response
     */
    public function indexAction()
    {
        $menuGroupMap  = $this->cmsService->getMenuGroupMap();
        $firstMenuItem = $menuGroupMap->getFirst()->getMenuItemMap()->getFirst();

        if ($firstMenuItem && $firstMenuItem->getId() != MenuConfig::MENU_ITEM_LOGOUT) {
            return $this->response->redirect($firstMenuItem->getRoute());
        }

        $this->view->title  = 'No available item found';
        $this->view->object = $this->view->title;

        $this->view->pick('cms/default');

        return null;
    }

    /**
     * Manages pages
     */
    public function pagesAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.pages');
        $this->view->object = (new Pages())->render();
        $this->view->pick('cms/default');
    }

    /**
     * Manage images and other files
     */
    public function mediaAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.media');
        $this->view->object = (new Finder())->render();
        $this->view->pick('cms/default');
    }

    /**
     * Manage Website/CMS settings
     */
    public function settingsAction()
    {
        $this->translationService->createSiteTranslationKeys();

        $this->view->title  = $this->translator->tl('menu.item.settings');
        $this->view->object = (new SettingsForm())->render();
        $this->view->pick('cms/settings');
    }

    /**
     * Manage users
     */
    public function usersAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.users');
        $this->view->object = (new Users())->render();
        $this->view->pick('cms/default');
    }

    /**
     * Show the website's visitors
     */
    public function statsAction()
    {
        if ( ! $this->acl->allowed(Permission::ACCESS_STATISTICS)) {
            throw new UnauthorizedException();
        }

        $this->view->title = $this->translator->tl('menu.item.stats');

        $startDate = $this->dateTimeService->getOneYearAgoFirstDayOfMonth();
        $maxDate   = $this->analyticsService->getMaxDate() ?: new DateTime();
        $minDate   = $this->analyticsService->getMinDate() ?: new DateTime();

        if ($startDate < $minDate) {
            $startDate = null;
        }

        $this->view->settings = [
            'dateFormat' => $this->translator->tl('system.momentJsDateFormat'),
            'startDate'  => $startDate ? $startDate->format(KikCMSConfig::DATE_FORMAT) : null,
            'maxDate'    => $maxDate->format(KikCMSConfig::DATE_FORMAT),
            'minDate'    => $minDate->format(KikCMSConfig::DATE_FORMAT),
        ];

        $this->assetService->addJs('https://www.gstatic.com/charts/loader.js');

        $this->view->pick('cms/statistics');
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    public function previewAction(PageLanguage $pageLanguage)
    {
        $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

        $this->response->redirect($url);
    }

    /**
     * @param null $languageCode
     * @return string
     */
    public function getTinyMceLinksAction($languageCode = null)
    {
        $languageCode = $languageCode ? $languageCode : $this->languageService->getDefaultLanguageCode();

        return json_encode($this->tinyMceService->getLinkList($languageCode));
    }

    /**
     * Get all translations for the given key in json format
     *
     * @return ResponseInterface
     */
    public function getTranslationsForKeyAction(): ResponseInterface
    {
        $key          = $this->request->getPost('key');
        $languages    = $this->languageService->getLanguages();
        $translations = [];

        foreach ($languages as $language) {
            $this->translator->setLanguageCode($language->code);
            $translations[(string) $language->code] = $this->translator->tl($key);
        }

        return $this->response->setJsonContent($translations);
    }

    /**
     * Get an url map (pageId => url) for given language
     *
     * @param string $langCode
     * @return ResponseInterface
     */
    public function getUrlsAction(string $langCode): ResponseInterface
    {
        return $this->response->setJsonContent($this->urlService->getUrlsByLangCode($langCode));
    }

    /**
     * Logout the CMS user
     */
    public function logoutAction()
    {
        $this->userService->logout();
    }

    /**
     * @return ResponseInterface
     */
    public function generateSecurityTokenAction(): ResponseInterface
    {
        if ( ! $this->acl->allowed(Permission::ACCESS_STATISTICS)) {
            return $this->response->setJsonContent(null);
        }

        $token = $this->cmsService->createSecurityToken();

        return $this->response->setJsonContent($token);
    }
}
