<?php declare(strict_types=1);

namespace KikCMS\Controllers;

use KikCMS\Services\Finder\FileRemoveService;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Services\Finder\FilePermissionService;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\TwigService;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Services\Finder\FileService;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Models\File;
use Phalcon\Http\ResponseInterface;

/**
 * @property AccessControl $acl
 * @property DbService $dbService
 * @property FileService $fileService
 * @property FileRemoveService $fileRemoveService
 * @property Translator $translator
 * @property MediaResizeBase $mediaResize
 * @property UserService $userService
 * @property FilePermissionService $filePermissionService
 * @property PageContentService $pageContentService
 * @property TwigService $twigService
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
            if ($errorMessage = $this->fileRemoveService->getDeleteErrorMessage($file)) {
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
     * @param File $file
     * @return string
     */
    public function fileAction(File $file)
    {
        if ( ! $this->filePermissionService->canRead($file)) {
            throw new UnauthorizedException();
        }

        return $this->response->redirect($this->fileService->getUrlCreateIfMissing($file, true));
    }

    /**
     * @param string $fileKey
     * @return string
     * @throws UnauthorizedException
     * @internal param int $fileId
     */
    public function keyAction(string $fileKey)
    {
        $file     = $this->fileService->getByKey($fileKey);
        $filePath = $this->fileService->getFilePath($file);

        if ( ! $this->filePermissionService->canRead($file)) {
            throw new UnauthorizedException();
        }

        return $this->outputFile($filePath, $file->getOutputMimeType(), $file->getName());
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
        $fileIds  = $this->request->getPost('fileIds');
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

        $uploadStatus = $finder->uploadFiles($uploadedFiles, $overwriteFileId);

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