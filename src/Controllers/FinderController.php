<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Models\File;
use Phalcon\Http\ResponseInterface;

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

        if ($folderId && ! $this->filePermissionService->canEditId($folderId)) {
            throw new UnauthorizedException();
        }

        $folderId = $this->fileService->createFolder($folderName, $folderId);

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

        $files = File::getByIdList($fileIds);

        foreach ($files as $file) {
            if ($errorMessage = $this->fileRemoveService->getDeleteErrorMessageForFile($file)) {
                $errorMessages[] = $errorMessage;
            } else {
                $idsToRemove[] = $file->getId();
            }
        }

        try {
            $this->fileRemoveService->deleteFilesByIds($idsToRemove);
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
        $fileId   = (int) $this->request->getPost('fileId', 'int');
        $fileName = $this->request->getPost('fileName');

        if ( ! $this->filePermissionService->canEditId($fileId)) {
            throw new UnauthorizedException();
        }

        $this->fileService->updateFileNameById($fileId, $fileName);

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => [$fileId]
        ]);
    }

    /**
     * @return string
     */
    public function editKeyAction()
    {
        $finder = $this->getRenderable();
        $fileId = (int) $this->request->getPost('fileId', 'int');
        $key    = $this->request->getPost('key');

        if ( ! $this->filePermissionService->canEditId($fileId)) {
            throw new UnauthorizedException();
        }

        $file      = File::getById($fileId);
        $file->key = $key;
        $file->save();

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => [$fileId]
        ]);
    }

    /**
     * @param File $file
     * @return ResponseInterface
     */
    public function fileAction(File $file): ResponseInterface
    {
        if ( ! $this->filePermissionService->canRead($file)) {
            throw new UnauthorizedException();
        }

        return $this->response->redirect($this->fileService->getUrlCreateIfMissing($file, true));
    }

    /**
     * @param string $fileKey
     * @return ResponseInterface
     * @throws UnauthorizedException
     * @internal param int $fileId
     */
    public function keyAction(string $fileKey): ResponseInterface
    {
        $file = $this->fileService->getByKey($fileKey);

        if ( ! $this->filePermissionService->canRead($file)) {
            throw new UnauthorizedException();
        }

        return $this->response->redirect($this->fileService->getUrlCreateIfMissing($file, true));
    }

    /**
     * @return string
     */
    public function openFolderAction()
    {
        $targetFolderId = (int) $this->request->getPost('folderId', 'int');

        if ($targetFolderId && ! $this->filePermissionService->canReadId($targetFolderId)) {
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
        $fileIds  = $this->request->getPost('fileIds') ?: [];
        $folderId = $finder->getFilters()->getFolderId();

        if ( ! $this->filePermissionService->canEditId($folderId)) {
            throw new UnauthorizedException();
        }

        $this->fileService->moveFilesToFolderById($fileIds, $folderId);

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
            $finder->getFilters()->setFolderId(null);
        }

        return json_encode([
            'files' => $finder->renderFiles(),
            'path'  => $finder->renderPath(),
        ]);
    }

    /**
     * @return string
     */
    public function uploadAction()
    {
        $finder          = $this->getRenderable();
        $uploadedFiles   = $this->request->getUploadedFiles();
        $overwriteFileId = (int) $this->request->getPost('overwriteFileId', 'int', null);

        $folderId = $finder->getFilters()->getFolderId();

        if ($folderId && ! $this->filePermissionService->canEditId($folderId)) {
            throw new UnauthorizedException();
        }

        $uploadStatus = $this->fileService->uploadFiles($uploadedFiles, $folderId, $overwriteFileId);

        return json_encode([
            'files'   => $finder->renderFiles(),
            'fileIds' => $uploadStatus->getFileIds(),
            'errors'  => $uploadStatus->getErrors(),
        ]);
    }

    /**
     * @param int $fileId
     * @return ResponseInterface
     */
    public function urlAction(int $fileId): ResponseInterface
    {
        return $this->response->setJsonContent(['url' => $this->twigService->mediaFile($fileId)]);
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