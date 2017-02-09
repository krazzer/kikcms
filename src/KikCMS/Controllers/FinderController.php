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
    public function deleteAction()
    {
        $finder  = new Finder();
        $fileIds = $this->request->getPost('fileIds');

        $this->finderFileService->deleteFilesByIds($fileIds);

        return json_encode(['files' => $finder->renderFiles()]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $finder = new Finder();

        $filters = [
            FinderConfig::FILTER_SEARCH => $this->request->getPost('search')
        ];

        return json_encode(['files' => $finder->renderFiles($filters)]);
    }

    /**
     * @return string
     */
    public function uploadAction()
    {
        $finder        = new Finder();
        $uploadedFiles = $this->request->getUploadedFiles();
        $uploadStatus  = $finder->uploadFiles($uploadedFiles);

        return json_encode([
            'uploadStatus' => $uploadStatus,
            'files'        => $finder->renderFiles(),
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
}