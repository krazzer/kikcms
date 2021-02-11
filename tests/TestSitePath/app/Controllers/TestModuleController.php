<?php


namespace Website\Controllers;


use Helpers\TestHelper;
use Phalcon\Http\ResponseInterface;
use Website\DataTables\PersonImages;
use Website\Forms\DataTableTestForm;
use Website\Forms\TestPersonForm;
use KikCMS\Controllers\BaseCmsController;
use Website\DataTables\DataTableTestObjects;

class TestModuleController extends BaseCmsController
{
    public function testDataTableAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'object'           => (new DataTableTestObjects)->render(),
            'selectedMenuItem' => 'datatabletest',
            'title'            => 'Test DataTable',
        ]);
    }

    public function testDataTableFormAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'object'           => (new DataTableTestForm)->render(),
            'selectedMenuItem' => 'datatabletestform',
            'title'            => 'Test DataTable Form',
        ]);
    }

    public function personFormAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'object'           => (new TestPersonForm)->render(),
            'selectedMenuItem' => 'personform',
            'title'            => 'Person Form',
        ]);
    }

    public function personImagesAction(): ResponseInterface
    {
        return $this->view('cms/default', [
            'object'           => (new PersonImages)->render(),
            'selectedMenuItem' => 'personimages',
            'title'            => 'Person images',
        ]);
    }

    public function outputFileAction(): string
    {
        $sitePath = (new TestHelper)->getSitePath();

        return $this->outputFile($sitePath . 'storage/media/1.png', 'image/png');
    }

    public function outputFileExceptionAction(): string
    {
        return $this->outputFile('/some/file', 'image/png');
    }

    public function outputCsvAction()
    {
        $this->outputCsv('csv', [['val1', 'val2']], ['key1', 'key2']);
    }
}