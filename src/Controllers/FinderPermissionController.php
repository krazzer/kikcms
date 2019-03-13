<?php

namespace KikCMS\Controllers;

use KikCMS\Services\Finder\FilePermissionHelper;
use KikCMS\Services\Finder\FilePermissionService;
use Phalcon\Http\ResponseInterface;

/**
 * @property FilePermissionService $filePermissionService
 * @property FilePermissionHelper $filePermissionHelper
 */
class FinderPermissionController extends BaseCmsController
{
    /**
     * @return ResponseInterface
     */
    public function getAction(): ResponseInterface
    {
        $fileIds = (array) $this->request->getPost('fileIds');

        $modalTitle      = $this->filePermissionHelper->getModalTitle($fileIds);
        $permissionTable = $this->filePermissionHelper->getPermissionTable($fileIds);

        return $this->response->setJsonContent([
            'title' => $modalTitle,
            'table' => $permissionTable,
        ]);
    }

    /**
     * Update file permissions
     */
    public function updateAction()
    {
        $permission      = (array) $this->request->getPost('permission');
        $fileIds         = (array) $this->request->getPost('fileIds');
        $saveRecursively = (bool) $this->request->getPost('recursive');

        $permissionList = $this->filePermissionHelper->convertDataToList($permission, $fileIds, $saveRecursively);
        $success        = $this->filePermissionService->updateByList($permissionList, $fileIds, $saveRecursively);

        return $this->response->setJsonContent([
            'success' => $success,
        ]);
    }
}