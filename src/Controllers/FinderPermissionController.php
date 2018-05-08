<?php

namespace KikCMS\Controllers;

use KikCMS\Classes\Finder\FinderPermissionHelper;
use KikCMS\Services\Finder\FinderPermissionService;
use Phalcon\Http\ResponseInterface;

/**
 * @property FinderPermissionService $finderPermissionService
 * @property FinderPermissionHelper $finderPermissionHelper
 */
class FinderPermissionController extends BaseCmsController
{
    /**
     * @return ResponseInterface
     */
    public function getAction(): ResponseInterface
    {
        $fileIds = (array) $this->request->getPost('fileIds');

        $modalTitle      = $this->finderPermissionHelper->getModalTitle($fileIds);
        $permissionTable = $this->finderPermissionHelper->getPermissionTable($fileIds);

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

        $permissionList = $this->finderPermissionHelper->convertDataToList($permission, $fileIds, $saveRecursively);
        $success        = $this->finderPermissionService->updateByList($permissionList, $fileIds);

        return $this->response->setJsonContent([
            'success' => $success,
        ]);
    }
}