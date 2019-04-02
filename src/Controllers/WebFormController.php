<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Finder\Finder;
use KikCMS\Services\Finder\FileService;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Models\File;

/**
 * @property FileService $fileService
 */
class WebFormController extends RenderableController
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
    public function getFilePreviewAction()
    {
        $fileId = $this->request->getPost('fileId');
        $file   = File::getById($fileId);
        $finder = new Finder();

        return json_encode([
            'preview'    => $finder->renderFilePreview($file),
            'dimensions' => $this->fileService->getThumbDimensions($file),
            'name'       => $file->getName(),
        ]);
    }

    /**
     * @return string
     */
    public function getFinderAction()
    {
        $finder = new Finder();
        $finder->setPickingMode(true);

        return json_encode([
            'finder' => $finder->render()
        ]);
    }

    /**
     * @return string
     */
    public function uploadAndPreviewAction()
    {
        $finder = new Finder();

        if ($folderId = $this->request->getPost('folderId')) {
            $finder->getFilters()->setFolderId($folderId);
        }

        $uploadedFiles = $this->request->getUploadedFiles();
        $uploadStatus  = $finder->uploadFiles($uploadedFiles);
        $fileIds       = $uploadStatus->getFileIds();

        $fileId = isset($fileIds[0]) ? $fileIds[0] : null;

        $result = [
            'fileId' => $fileId,
            'errors' => $uploadStatus->getErrors(),
        ];

        if ($file = File::getById($fileId)) {
            $result['preview']    = $finder->renderFilePreview($file);
            $result['dimensions'] = $this->fileService->getThumbDimensions($file);
            $result['name']       = $file->getName();
        }

        return json_encode($result);
    }

    /**
     * @inheritdoc
     * @return Renderable|Webform
     */
    protected function getRenderable(): Renderable
    {
        return parent::getRenderable();
    }
}