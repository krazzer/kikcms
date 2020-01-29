<?php declare(strict_types=1);

namespace KikCMS\Controllers;


use KikCMS\Classes\Finder\Finder;
use KikCMS\Services\Finder\FileService;
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
     * @param File $file
     * @return string
     */
    public function getFilePreviewAction(File $file)
    {
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
            'finder' => $this->view->getPartial('webform/finder', [
                'finder' => $finder->render()
            ])
        ]);
    }

    /**
     * @return string
     */
    public function uploadAndPreviewAction()
    {
        $finder = new Finder();

        if ($folderId = $this->request->getPost('folderId')) {
            $finder->getFilters()->setFolderId((int)$folderId);
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
}