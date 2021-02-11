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

        return $this->view('cms/filePicker', [
            'title'  => $this->translator->tl('menu.item.media'),
            'finder' => $finder->render(),
        ]);
    }

    /**
     * First page to show when the user logs in, to avoid POST reset, redirect.
     *
     * @return ResponseInterface|null
     */
    public function indexAction(): ?ResponseInterface
    {
        $menuGroupMap  = $this->cmsService->getMenuGroupMap();
        $firstMenuItem = $menuGroupMap->getFirst()->getMenuItemMap()->getFirst();

        if ($firstMenuItem && $firstMenuItem->getId() != MenuConfig::MENU_ITEM_LOGOUT) {
            return $this->response->redirect($firstMenuItem->getRoute());
        }

        return $this->view('cms/default', [
            'title'  => 'No available item found',
            'object' => 'No available item found'
        ], 200);
    }

    /**
     * Manages pages
     */
    public function pagesAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'title'  => $this->translator->tl('menu.item.pages'),
            'object' => (new Pages)->render(),
        ]);
    }

    /**
     * Manage images and other files
     * @return ResponseInterface
     */
    public function mediaAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'title'  => $this->translator->tl('menu.item.media'),
            'object' => (new Finder)->render(),
        ]);
    }

    /**
     * Manage Website/CMS settings
     * @return ResponseInterface
     */
    public function settingsAction(): ResponseInterface
    {
        $this->translationService->createSiteTranslationKeys();

        return $this->view('cms/settings', [
            'title'  => $this->translator->tl('menu.item.settings'),
            'object' => (new SettingsForm)->render(),
        ]);
    }

    /**
     * Manage users
     * @return ResponseInterface
     */
    public function usersAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'title'  => $this->translator->tl('menu.item.users'),
            'object' => (new Users)->render(),
        ]);
    }

    /**
     * Show the website's visitors
     * @return ResponseInterface
     */
    public function statsAction(): ResponseInterface
    {
        if ( ! $this->acl->allowed(Permission::ACCESS_STATISTICS)) {
            throw new UnauthorizedException();
        }

        $startDate = $this->dateTimeService->getOneYearAgoFirstDayOfMonth();
        $maxDate   = $this->analyticsService->getMaxDate() ?: new DateTime();
        $minDate   = $this->analyticsService->getMinDate() ?: new DateTime();

        if ($startDate < $minDate) {
            $startDate = null;
        }

        $settings = [
            'dateFormat' => $this->translator->tl('system.momentJsDateFormat'),
            'startDate'  => $startDate ? $startDate->format(KikCMSConfig::DATE_FORMAT) : null,
            'maxDate'    => $maxDate->format(KikCMSConfig::DATE_FORMAT),
            'minDate'    => $minDate->format(KikCMSConfig::DATE_FORMAT),
        ];

        $this->assetService->addJs('https://www.gstatic.com/charts/loader.js');

        return $this->view('cms/statistics', [
            'title'    => $this->translator->tl('menu.item.stats'),
            'settings' => $settings,
        ]);
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
     * @return ResponseInterface
     */
    public function getTinyMceLinksAction($languageCode = null): ResponseInterface
    {
        $languageCode = $languageCode ? $languageCode : $this->languageService->getDefaultLanguageCode();

        return $this->response->setJsonContent($this->tinyMceService->getLinkList($languageCode));
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
     * @return ResponseInterface
     */
    public function logoutAction(): ResponseInterface
    {
        $this->userService->logout();

        return $this->response->redirect('cms/login');
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
