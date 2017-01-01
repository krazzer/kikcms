<?php

namespace KikCMS\Controllers;

use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Products;
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
        return $this->response->redirect('cms/' . MenuConfig::MENU_ITEM_MAIN_MENU);
    }

    public function menuAction()
    {
        $datatable = new Products();

        $this->view->datatable = $datatable->render();
        $this->view->pick('cms/default');
    }

    public function mediaAction()
    {
        $this->view->pick('cms/default');
    }

    public function logoutAction()
    {
        $this->userService->logout();
    }
}
