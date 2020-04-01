<?php
declare(strict_types=1);

namespace Controllers;

use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Controllers\DataTableController;
use KikCMS\DataTables\Pages;
use Phalcon\Http\Request;

class DataTableControllerTest extends Unit
{
    public function testAddImageAction()
    {
        $dataTableController = new DataTableController();

        $acl = $this->createMock(AccessControl::class);
        $acl->method('dataTableAllowed')->willReturn(true);

        $request = $this->createMock(Request::class);
//        $request->method('getPost')->with(null)->willReturn([]);
        $request->method('getPost')
            ->withConsecutive(['renderableClass'], ['renderableInstance'], ['renderableClass'], [])
            ->willReturnOnConsecutiveCalls(Pages::class, 'instance', Pages::class, []);

        $dataTableController->acl        = $acl;
        $dataTableController->request    = $request;
        $dataTableController->translator = (new TestHelper)->getTranslator();

        $dataTableController->addImageAction();
    }

    public function testDeleteAction()
    {
        $dataTableController = new DataTableController();

        $dataTableController->addImageAction();
    }

    public function testUploadImagesAction()
    {

    }

    public function testRearrangeAction()
    {

    }

    public function testSaveAction()
    {

    }

    public function testGetRenderable()
    {

    }
}
