<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Finder\Finder;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Products;
use KikCMS\Forms\SettingsForm;
use KikCMS\Services\UserService;
use Phalcon\Config;
use Phalcon\Http\Response;

/**
 * @property Config $config
 * @property UserService $userService
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

    public function productsAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.products');
        $this->view->object = (new Products())->render();
        $this->view->pick('cms/default');
    }

    public function mediaAction()
    {
        $this->view->title  = $this->translator->tl('media.title');
        $this->view->object = (new Finder())->render();
        $this->view->pick('cms/default');
    }

    public function settingsAction()
    {
        $this->view->title  = $this->translator->tl('menu.item.settings');
        $this->view->object = (new SettingsForm())->render();
        $this->view->pick('cms/default');
    }

    public function logoutAction()
    {
        $this->userService->logout();
    }
}
