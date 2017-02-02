<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Finder\Finder;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Products;
use KikCMS\Forms\ProductForm;
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

    public function formAction()
    {
        $dataForm = new ProductForm();
        $dataForm->addTextField('title', "Naam product");

        $this->view->form = $dataForm->renderWithData(23);

        $this->view->pick('cms/form');
    }

    public function mediaAction()
    {
        $finder = new Finder();

        $this->view->finder = $finder->render();
        $this->view->pick('cms/media');
    }

    public function logoutAction()
    {
        $this->userService->logout();
    }
}
