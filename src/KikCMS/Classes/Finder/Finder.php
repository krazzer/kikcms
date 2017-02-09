<?php

namespace KikCMS\Classes\Finder;


use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;

/**
 * @property FinderFileService $finderFileService
 */
class Finder extends Injectable
{
    const JS_TRANSLATIONS = [
        'media.deleteConfirm',
        'media.deleteConfirmOne',
        'media.createFolder',
        'media.defaultFolderName',
    ];

    /**
     * @param array $filters
     * @return string
     */
    public function render($filters = [])
    {
        $this->addAssets();

        $files  = $this->finderFileService->getByFilters($filters);
        $thumbs = $this->finderFileService->getThumbNailMap($files);

        return $this->renderView('index', [
            'files'          => $files,
            'thumbnails'     => $thumbs,
            'instance'       => $this->getInstance(),
            'maxFileUploads' => $this->getMaxFileUploads(),
        ]);
    }

    /**
     * @param array $filters
     * @return string
     */
    public function renderFiles($filters = [])
    {
        $files  = $this->finderFileService->getByFilters($filters);
        $thumbs = $this->finderFileService->getThumbNailMap($files);

        return $this->renderView('files', [
            'files'      => $files,
            'thumbnails' => $thumbs,
        ]);
    }

    /**
     * @param $folderId
     * @return string
     */
    public function renderPath(int $folderId)
    {
        $path = $this->finderFileService->getFolderPath($folderId);
        $path = array_reverse($path, true);

        if (count($path) == 1) {
            return '';
        }

        return $this->renderView('path', [
            'path'            => $path,
            'currentFolderId' => $folderId
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
     * @param File[] $files
     * @param int $folderId
     * @return array with the status for each file i.e.: [0 => 123, 1 => false] number is new finderId, false is fail
     */
    public function uploadFiles(array $files, $folderId = 0)
    {
        $uploadStatus = [];

        foreach ($files as $index => $file) {
            $result = $this->finderFileService->create($file, $folderId);

            $uploadStatus[$index] = $result;
        }

        return $uploadStatus;
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

        $this->view->jsTranslations = array_merge($this->view->jsTranslations, self::JS_TRANSLATIONS);
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