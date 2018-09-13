<?php

namespace KikCMS\Controllers;

use KikCMS\Services\Finder\FinderFileRemoveService;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Services\Finder\FinderPermissionService;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Services\Finder\FinderFileService;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Models\FinderFile;

/**
 * @property AccessControl $acl
 * @property DbService $dbService
 * @property FinderFileService $finderFileService
 * @property FinderFileRemoveService $finderFileRemoveService
 * @property Translator $translator
 * @property MediaResizeBase $mediaResize
 * @property UserService $userService
 * @property FinderPermissionService $finderPermissionService
 * @property PageContentService $pageContentService
 */
class FinderController extends RenderableController
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->view->disable();
    }

    /**
     * @return string
     */
    public function createFolderAction()
    {
        $finder     = $this->getRenderable();
        $folderName = $this->request->getPost('folderName');
        $folderId   = $finder->getFilters()->getFolderId();

        if ($folderId && ! $this->finderPermissionService->canEditId($folderId)) {
            throw new UnauthorizedException();
        }

        $folderId = $this->finderFileService->createFolder($folderName, $folderId);

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => [$folderId],
        ]);
    }

    /**
     * @return string
     */
    public function deleteAction()
    {
        $finder        = $this->getRenderable();
        $fileIds       = $this->request->getPost('fileIds', 'int');
        $errorMessages = [];
        $idsToRemove   = [];

        $files = FinderFile::getByIdList($fileIds);

        foreach ($files as $file) {
            if ($errorMessage = $this->finderFileRemoveService->getDeleteErrorMessage($file)) {
                $errorMessages[] = $errorMessage;
            } else {
                $idsToRemove[] = $file->getId();
            }
        }

        try {
            $this->finderFileRemoveService->deleteFilesByIds($idsToRemove);
        } catch (DbForeignKeyDeleteException $e) {
            $errorMessages[] = $this->translator->tl('media.deleteErrorLinked');
        }

        return json_encode([
            'files'         => $finder->renderFiles(),
            'errorMessages' => $errorMessages
        ]);
    }

    /**
     * @return string
     */
    public function editFileNameAction()
    {
        $finder   = $this->getRenderable();
        $fileId   = $this->request->getPost('fileId');
        $fileName = $this->request->getPost('fileName');

        if ( ! $this->finderPermissionService->canEditId($fileId)) {
            throw new UnauthorizedException();
        }

        $this->finderFileService->updateFileNameById($fileId, $fileName);

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => [$fileId]
        ]);
    }

    /**
     * @param FinderFile $finderFile
     * @return string
     * @throws NotFoundException
     * @internal param int $fileId
     */
    public function fileAction(FinderFile $finderFile)
    {
        $filePath = $this->finderFileService->getFilePath($finderFile);

        if ( ! $this->finderPermissionService->canRead($finderFile)) {
            throw new UnauthorizedException();
        }

        return $this->outputFile($filePath, $finderFile->getMimeType(), $finderFile->getName());
    }

    /**
     * @param string $fileKey
     * @return string
     * @throws UnauthorizedException
     * @internal param int $fileId
     */
    public function keyAction(string $fileKey)
    {
        $finderFile = $this->finderFileService->getByKey($fileKey);
        $filePath   = $this->finderFileService->getFilePath($finderFile);

        if ( ! $this->finderPermissionService->canRead($finderFile)) {
            throw new UnauthorizedException();
        }

        return $this->outputFile($filePath, $finderFile->getMimeType(), $finderFile->getName());
    }

    /**
     * @return string
     */
    public function openFolderAction()
    {
        $targetFolderId = $this->request->getPost('folderId', 'int');

        if ($targetFolderId && ! $this->finderPermissionService->canReadId($targetFolderId)) {
            throw new UnauthorizedException();
        }

        $this->session->finderFolderId = $targetFolderId;

        $finder = $this->getRenderable();

        return json_encode([
            'files' => $finder->renderFiles(),
            'path'  => $finder->renderPath(),
        ]);
    }

    /**
     * @return string
     */
    public function pasteAction()
    {
        $finder   = $this->getRenderable();
        $fileIds  = $this->request->getPost('fileIds');
        $folderId = $finder->getFilters()->getFolderId();

        if ( ! $this->finderPermissionService->canEditId($folderId)) {
            throw new UnauthorizedException();
        }

        $this->finderFileService->moveFilesToFolderById($fileIds, $folderId);

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => $fileIds,
        ]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $finder = $this->getRenderable();

        if ($finder->getFilters()->getSearch()) {
            $finder->getFilters()->setFolderId(0);
        }

        return json_encode([
            'files' => $finder->renderFiles(),
            'path'  => $finder->renderPath(),
        ]);
    }

    /**
     * @param int $fileId
     * @param string|null $type
     * @return string
     * @throws NotFoundException
     */
    public function thumbAction(int $fileId, string $type = null)
    {
        /** @var FinderFile $finderFile */
        if (( ! $finderFile = FinderFile::getById($fileId)) || ! $this->mediaResize->typeExists($type)) {
            throw new NotFoundException();
        }

        $thumbPath = $this->finderFileService->getThumbPath($finderFile, $type);

        if ( ! file_exists($thumbPath)) {
            $this->finderFileService->createThumb($finderFile, $type);
        }

        return $this->outputFile($thumbPath, $finderFile->getMimeType(), $finderFile->getName());
    }

    /**
     * @return string
     */
    public function uploadAction()
    {
        $finder          = $this->getRenderable();
        $uploadedFiles   = $this->request->getUploadedFiles();
        $folderId        = $finder->getFilters()->getFolderId();
        $overwriteFileId = $this->request->getPost('overwriteFileId', 'int', null);

        if ($folderId && ! $this->finderPermissionService->canEditId($folderId)) {
            throw new UnauthorizedException();
        }

        $uploadStatus = $finder->uploadFiles($uploadedFiles, $overwriteFileId);

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => $uploadStatus->getFileIds(),
            'errors'  => $uploadStatus->getErrors(),
        ]);
    }

    /**
     * @inheritdoc
     * @return Finder|Renderable
     */
    protected function getRenderable(): Renderable
    {
        /** @var Finder $finder */
        $finder = parent::getRenderable();

        if ( ! $finder->allowedInCurrentFolder()) {
            throw new UnauthorizedException();
        }

        return $finder;
    }
}