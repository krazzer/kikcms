<?php


namespace Website\Controllers;


use KikCMS\Controllers\BaseCmsController;
use Website\DataTables\DataTableTestObjects;

class ModuleController extends BaseCmsController
{
    public function testDataTableAction()
    {
        $this->view->object           = (new DataTableTestObjects)->render();
        $this->view->selectedMenuItem = 'datatabletest';
        $this->view->title            = 'Test DataTable';

        $this->view->pick('cms/default');
    }
}