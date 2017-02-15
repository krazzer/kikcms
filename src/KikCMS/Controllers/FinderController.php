<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DbService;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderFile;

/**
 * @property DbService $dbService
 * @property FinderFileService $finderFileService
 */
class FinderController extends BaseController
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
        $finder     = new Finder();
        $folderName = $this->request->getPost('folderName');
        $filters    = $this->getFilters();

        $folderId = $this->finderFileService->createFolder($folderName, $filters[FinderConfig::FILTER_FOLDER_ID]);

        return json_encode([
            'files'   => $finder->renderFiles($filters),
            'fileIds' => [$folderId],
        ]);
    }

    /**
     * @return string
     */
    public function deleteAction()
    {
        $finder  = new Finder();
        $fileIds = $this->request->getPost('fileIds');
        $filters = $this->getFilters();

        $this->finderFileService->deleteFilesByIds($fileIds);

        return json_encode(['files' => $finder->renderFiles($filters)]);
    }

    /**
     * @return string
     */
    public function editFileNameAction()
    {
        $finder   = new Finder();
        $fileId   = $this->request->getPost('fileId');
        $fileName = $this->request->getPost('fileName');
        $filters  = $this->getFilters();

        $this->finderFileService->updateFileNameById($fileId, $fileName);

        return json_encode([
            'files'   => $finder->renderFiles($filters),
            'fileIds' => [$fileId]
        ]);
    }

    /**
     * @param int $fileId
     * @return string
     * @throws NotFoundException
     */
    public function fileAction(int $fileId)
    {
        /** @var FinderFile $finderFile */
        if ( ! $finderFile = FinderFile::getById($fileId)) {
            throw new NotFoundException();
        }

        $filePath = $this->finderFileService->getFilePath($finderFile);

        return $this->outputFile($filePath, $finderFile->getMimeType(), $finderFile->getName());
    }

    /**
     * @return string
     */
    public function openFolderAction()
    {
        $finder  = new Finder();
        $filters = $this->getFilters();

        return json_encode([
            'files' => $finder->renderFiles($filters),
            'path'  => $finder->renderPath($filters[FinderConfig::FILTER_FOLDER_ID]),
        ]);
    }

    /**
     * @return string
     */
    public function pasteAction()
    {
        $finder  = new Finder();
        $filters = $this->getFilters();
        $fileIds = $this->request->getPost('fileIds');

        $this->finderFileService->moveFilesToFolderById($fileIds, $filters[FinderConfig::FILTER_FOLDER_ID]);

        return json_encode([
            'files'   => $finder->renderFiles($filters),
            'fileIds' => $fileIds,
        ]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $finder  = new Finder();
        $filters = $this->getFilters();

        if ($filters[FinderConfig::FILTER_SEARCH]) {
            $path = $finder->renderPath(0);
        } else {
            $path = $finder->renderPath($filters[FinderConfig::FILTER_FOLDER_ID]);
        }

        return json_encode([
            'files' => $finder->renderFiles($filters),
            'path'  => $path,
        ]);
    }

    /**
     * @param int $fileId
     * @return string
     * @throws NotFoundException
     */
    public function thumbAction(int $fileId)
    {
        /** @var FinderFile $finderFile */
        if ( ! $finderFile = FinderFile::getById($fileId)) {
            throw new NotFoundException();
        }

        $thumbPath = $this->finderFileService->getThumbPath($finderFile);

        if ( ! file_exists($thumbPath)) {
            $this->finderFileService->createThumb($finderFile);
        }

        return $this->outputFile($thumbPath, $finderFile->getMimeType(), $finderFile->getName());
    }

    /**
     * @return string
     */
    public function uploadAction()
    {
        $finder = new Finder();

        $uploadedFiles = $this->request->getUploadedFiles();

        $filters      = $this->getFilters();
        $uploadStatus = $finder->uploadFiles($uploadedFiles, $filters[FinderConfig::FILTER_FOLDER_ID]);

        return json_encode([
            'files'   => $finder->renderFiles($filters),
            'fileIds' => $uploadStatus->getFileIds(),
            'errors'  => $uploadStatus->getErrors(),
        ]);
    }

    /**
     * @return array
     */
    private function getFilters(): array
    {
        $filters = [];

        if ($this->request->hasPost('folderId')) {
            $filters[FinderConfig::FILTER_FOLDER_ID] = $this->request->getPost('folderId');
        }

        if ($this->request->hasPost('search') && $this->request->getPost('search')) {
            $filters[FinderConfig::FILTER_SEARCH] = $this->request->getPost('search');
        }

        return $filters;
    }
}