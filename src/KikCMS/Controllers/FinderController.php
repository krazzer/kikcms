<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DbService;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Finder\FinderFileService;
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
    public function uploadAction()
    {
        $finder = new Finder();

        $finder->uploadFiles($this->request->getUploadedFiles());

        return '';
    }

    /**
     * @param int $fileId
     * @throws NotFoundException
     */
    public function thumbAction(int $fileId)
    {
        /** @var FinderFile $finderFile */
        if ( ! $finderFile = FinderFile::getById($fileId)) {
            throw new NotFoundException();
        }

        $filePath = $this->finderFileService->getThumbPath($finderFile);

        //todo: make easy way to output files
        $this->response->setContentType($finderFile->getMimeType());
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $finderFile->getName() . '"');
        $this->response->setHeader('Cache-control', 'max-age=2592000, public');
        $this->response->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime('+1 years')) . ' GMT');
        $this->response->setHeader('Pragma', 'cache');

        echo file_get_contents($filePath);
    }
}