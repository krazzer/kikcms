<?php

namespace KikCMS\Controllers;

use DateTime;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
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
use KikCMS\Services\UserService;
use KikCMS\Services\Util\DateTimeService;
use Phalcon\Http\Response;

/**
 * @property UserService $userService
 * @property UrlService $urlService
 * @property Translator $translator
 * @property LanguageService $languageService
 * @property TinyMceService $tinyMceService
 * @property DateTimeService $dateTimeService
 * @property AnalyticsService $analyticsService
 * @property WebsiteSettingsBase $websiteSettings
 * @property CmsService $cmsService
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
        $menuGroups    = $this->cmsService->getMenuItemGroups();
        $menuFirstKey  = array_keys($menuGroups)[0];
        $firstMenuItem = $menuGroups[$menuFirstKey]->getFirst();

        if($firstMenuItem && $firstMenuItem->getId() != MenuConfig::MENU_ITEM_LOGOUT){
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
        $this->view->title  = $this->translator->tl('menu.item.settings');
        $this->view->object = (new SettingsForm())->render();
        $this->view->pick('cms/default');
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
        $this->view->title = $this->translator->tl('menu.item.stats');

        $startDate = $this->dateTimeService->getOneYearAgoFirstDayOfMonth();
        $maxDate   = $this->analyticsService->getMaxDate() ?: new DateTime();
        $minDate   = $this->analyticsService->getMinDate() ?: new DateTime();

        if ($startDate < $minDate) {
            $startDate = null;
        }

        $this->view->jsTranslations = array_merge($this->view->jsTranslations, [
            'statistics.fetchingNewData',
            'statistics.fetchingFailed',
            'statistics.fetchNewData',
            'statistics.visitors',
        ]);

        $this->view->settings = [
            'dateFormat' => $this->translator->tl('system.momentJsDateFormat'),
            'startDate'  => $startDate ? $startDate->format(KikCMSConfig::DATE_FORMAT) : null,
            'maxDate'    => $maxDate->format(KikCMSConfig::DATE_FORMAT),
            'minDate'    => $minDate->format(KikCMSConfig::DATE_FORMAT),
        ];

        $this->view->pick('cms/statistics');
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    public function previewAction(PageLanguage $pageLanguage)
    {
        $url = $this->urlService->getUrlByPageLanguage($pageLanguage, false);

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
     * @return string
     */
    public function getTranslationsForKeyAction()
    {
        $key          = $this->request->getPost('key');
        $languages    = $this->languageService->getLanguages();
        $translations = [];

        foreach ($languages as $language) {
            $this->translator->setLanguageCode($language->code);
            $translations[(string) $language->code] = $this->translator->tl($key);
        }

        return json_encode($translations);
    }

    /**
     * Logout the CMS user
     */
    public function logoutAction()
    {
        $this->userService->logout();
    }
}
