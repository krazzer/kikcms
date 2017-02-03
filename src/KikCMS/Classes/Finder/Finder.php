<?php

namespace KikCMS\Classes\Finder;


use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;

/**
 * @property FinderFileService $finderFileService
 */
class Finder extends Injectable
{
    /**
     * @return string
     */
    public function render()
    {
        $this->addAssets();

        $files  = $this->finderFileService->getByDir();
        $thumbs = $this->finderFileService->getThumbNailMap($files);

        return $this->renderView('index', [
            'instance'       => $this->getInstance(),
            'files'          => $files,
            'thumbnails'     => $thumbs,
            'maxFileUploads' => $this->getMaxFileUploads(),
        ]);
    }

    /**
     * @return string
     */
    public function renderFiles()
    {
        $files  = $this->finderFileService->getByDir();
        $thumbs = $this->finderFileService->getThumbNailMap($files);

        return $this->renderView('files', [
            'files'      => $files,
            'thumbnails' => $thumbs,
        ]);
    }

    /**
     * Renders a view
     *
     * @param $viewName
     * @param array $parameters
     *
     * @return string
     */
    public function renderView($viewName, array $parameters = []): string
    {
        return $this->view->getPartial('finder/' . $viewName, $parameters);
    }

    /**
     * Creates an unique id for the finder js class so multiple instances don't get mixed up
     *
     * @return string
     */
    private function getInstance()
    {
        return uniqid('finder');
    }

    /**
     * Adds html/css required for finder
     */
    private function addAssets()
    {
        $this->view->assets->addCss('cmsassets/css/toolbarComponent.css');
        $this->view->assets->addCss('cmsassets/css/finder.css');
        $this->view->assets->addJs('cmsassets/js/finder/finder.js');
    }

    /**
     * @param File[] $files
     * @return array with the status for each file i.e.: [0 => 123, 1 => false] number is new finderId, false is fail
     */
    public function uploadFiles(array $files)
    {
        $uploadStatus = [];

        foreach ($files as $index => $file) {
            $result = $this->finderFileService->create($file);

            $uploadStatus[$index] = $result;
        }

        return $uploadStatus;
    }

    /**
     * @return int
     */
    private function getMaxFileUploads(): int
    {
        $maxFileUploads = ini_get('max_file_uploads');

        if ( ! $maxFileUploads) {
            return 20;
        }

        return $maxFileUploads;
    }
}