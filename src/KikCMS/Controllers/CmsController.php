<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\Forms\SettingsForm;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\UserService;
use Phalcon\Config;
use Phalcon\Http\Response;

/**
 * @property Config $config
 * @property UserService $userService
 * @property UrlService $urlService
 */
class CmsController extends BaseCmsController
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->response->redirect('cms/' . MenuConfig::MENU_ITEM_PAGES);
    }

    public function pagesAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.pages');
        $this->view->object = (new Pages())->render();
        $this->view->pick('cms/default');
    }

    public function mediaAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.media');
        $this->view->object = (new Finder())->render();
        $this->view->pick('cms/default');
    }

    public function settingsAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.settings');
        $this->view->object = (new SettingsForm())->render();
        $this->view->pick('cms/default');
    }

    public function statsIndexAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.statsIndex');
        $this->view->object = '<img src="https://cdn.meme.am/cache/instances/folder524/30938524.jpg" />';
        $this->view->pick('cms/default');
    }

    public function statsSourcesAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.statsSources');
        $this->view->object = '<img src="https://cdn.meme.am/cache/instances/folder524/30938524.jpg" />';
        $this->view->pick('cms/default');
    }

    public function usersAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.users');
        $this->view->object = '<img src="https://cdn.meme.am/cache/instances/folder524/30938524.jpg" />';
        $this->view->pick('cms/default');
    }

    /**
     * @param int $pageLanguageId
     * @throws NotFoundException
     */
    public function previewAction(int $pageLanguageId)
    {
        /** @var PageLanguage $pageLanguage */
        $pageLanguage = PageLanguage::getById($pageLanguageId);

        if ( ! $pageLanguage) {
            throw new NotFoundException();
        }

        $url = $this->urlService->getUrlByPageLanguage($pageLanguage);

        $this->response->redirect($url);
    }

    public function logoutAction()
    {
        $this->userService->logout();
    }
}
