<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCmsCore\Services\DbService;
use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Models\FinderFile;

/**
 * @property AccessControl $acl
 * @property DbService $dbService
 * @property FinderFileService $finderFileService
 * @property Translator $translator
 * @property MediaResizeBase $mediaResize
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
        $finder       = $this->getRenderable();
        $fileIds      = $this->request->getPost('fileIds');
        $errorMessage = null;

        try {
            $this->finderFileService->deleteFilesByIds($fileIds);
        } catch (DbForeignKeyDeleteException $e) {
            $errorMessage = $this->translator->tl('media.deleteErrorLinked');
        }

        return json_encode([
            'files'        => $finder->renderFiles(),
            'errorMessage' => $errorMessage
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

        if ( ! file_exists($filePath)) {
            throw new NotFoundException();
        }

        return $this->outputFile($filePath, $finderFile->getMimeType(), $finderFile->getName());
    }

    /**
     * @return string
     */
    public function openFolderAction()
    {
        $this->session->finderFolderId = (int) $this->request->getPost('folderId');

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
        $finder        = $this->getRenderable();
        $uploadedFiles = $this->request->getUploadedFiles();
        $uploadStatus  = $finder->uploadFiles($uploadedFiles);

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
        if ( ! $this->acl->allowedFinder()) {
            throw new UnauthorizedException();
        }

        return parent::getRenderable();
    }
}