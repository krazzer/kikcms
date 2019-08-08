<?php


namespace Website\Controllers;


use Website\Forms\PersonForm;
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

    public function personFormAction()
    {
        $this->view->object           = (new PersonForm)->render();
        $this->view->selectedMenuItem = 'personform';
        $this->view->title            = 'Person Form';

        $this->view->pick('cms/default');
    }
}