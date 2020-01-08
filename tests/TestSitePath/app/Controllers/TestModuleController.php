<?php


namespace Website\Controllers;


use Helpers\TestHelper;
use Website\Forms\TestPersonForm;
use KikCMS\Controllers\BaseCmsController;
use Website\DataTables\DataTableTestObjects;

class TestModuleController extends BaseCmsController
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
        $this->view->object           = (new TestPersonForm)->render();
        $this->view->selectedMenuItem = 'personform';
        $this->view->title            = 'Person Form';

        $this->view->pick('cms/default');
    }

    public function outputFileAction()
    {
        $sitePath = (new TestHelper)->getSitePath();

        return $this->outputFile($sitePath . 'storage/media/1.png', 'image/png');
    }

    public function outputFileExceptionAction()
    {
        return $this->outputFile('/some/file', 'image/png');
    }

    public function outputCsvAction()
    {
        $this->outputCsv('csv', [['val1', 'val2']], ['key1', 'key2']);
    }
}