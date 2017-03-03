<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Finder\Finder;
use KikCMS\Config\MenuConfig;
use KikCMS\DataTables\Pages;
use KikCMS\DataTables\Products;
use KikCMS\DataTables\Templates;
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
        return $this->response->redirect('cms/' . MenuConfig::MENU_ITEM_PAGES);
    }

    public function pagesAction()
    {
        $datatable = new Pages();

        $this->view->datatable = $datatable->render();
        $this->view->instance  = $datatable->getInstanceName();

        $this->view->pick('cms/pagesDataTable');
    }

    public function productsAction()
    {
        $datatable = new Products();

        $this->view->datatable = $datatable->render();
        $this->view->pick('cms/default');
    }

    public function formAction()
    {
        $dataForm = new ProductForm();
        $dataForm->addTextField('title', "Naam product");
        $dataForm->getFilters()->setEditId(23);

        $this->view->form = $dataForm->renderWithData();
        $this->view->pick('cms/form');
    }

    public function mediaAction()
    {
        $finder = new Finder();

        $this->view->finder = $finder->render();
        $this->view->pick('cms/media');
    }

    public function templatesAction()
    {
        $this->view->datatable = (new Templates())->render();
        $this->view->pick('cms/default');
    }

    public function logoutAction()
    {
        $this->userService->logout();
    }
}
