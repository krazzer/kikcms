<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Finder\Finder;
use KikCMS\Services\Finder\FinderFileService;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Models\FinderFile;

/**
 * @property FinderFileService $finderFileService
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
        $fileId     = $this->request->getPost('fileId');
        $finderFile = FinderFile::getById($fileId);
        $finder     = new Finder();

        return json_encode([
            'preview'    => $finder->renderFilePreview($finderFile),
            'dimensions' => $this->finderFileService->getThumbDimensions($finderFile),
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

        if($folderId = $this->request->getPost('folderId')){
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

        if ($fileId && $finderFile = FinderFile::getById($fileId)) {
            $result['preview']    = $finder->renderFilePreview($finderFile);
            $result['dimensions'] = $this->finderFileService->getThumbDimensions($finderFile);
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