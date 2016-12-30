<?php

namespace KikCMS\Controllers;

use KikCMS\Config\MenuConfig;
use KikCMS\Datatables\Products;
use KikCMS\Services\UserService;
use Phalcon\Config;
use Phalcon\Http\Response;

/**
 * @property Config $config
 * @property UserService $userService
 */
class CmsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        $this->view->setVar("menuStructure", MenuConfig::MENU_STRUCTURE);
        $this->view->setVar("currentAction", $this->dispatcher->getActionName());
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        $this->response->redirect('cms/' . MenuConfig::MENU_ITEM_MAIN_MENU);
    }

    public function mainMenuAction()
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
