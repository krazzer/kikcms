<?php
declare(strict_types=1);

use Helpers\Unit;
use KikCMS\Classes\Finder\UploadStatus;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Controllers\DataTableController;
use KikCMS\Services\Finder\FileService;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Website\DataTables\PersonImages;

class DataTableControllerTest extends Unit
{
    public function testUploadImagesAction()
    {
        $dataTableController = new DataTableController();

        $acl = $this->createMock(AccessControl::class);
        $acl->method('dataTableAllowed')->willReturn(true);

        $request = $this->createMock(Request::class);
        $request->method('getPost')->willReturnOnConsecutiveCalls(PersonImages::class, 'instance', PersonImages::class, []);
        $request->method('getUploadedFiles')->willReturn([]);

        $uploadStatus = new UploadStatus();
        $uploadStatus->addError('error');

        $fileService = $this->createMock(FileService::class);
        $fileService->method('uploadFiles')->willReturn($uploadStatus);

        $jsonResponse = new Response();
        $jsonResponse->setContent('json');

        $response = $this->createMock(Response::class);
        $response->method('setJsonContent')->willReturn($jsonResponse);

        $dataTableController->acl         = $acl;
        $dataTableController->request     = $request;
        $dataTableController->fileService = $fileService;
        $dataTableController->response    = $response;

        $this->assertEquals('json', $dataTableController->uploadImagesAction()->getContent());
    }
}
