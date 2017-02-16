<?php

namespace KikCMS\Controllers;


use InvalidArgumentException;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Classes\Model\Model;
use KikCMS\Classes\WebForm\Fields\Autocomplete;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Models\FinderFile;

/**
 * @property DbService dbService
 * @property FinderFileService finderFileService
 */
class WebFormController extends BaseController
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
    public function getAutocompleteDataAction()
    {
        $fieldKey = $this->request->getPost('field');
        $webForm  = $this->getWebForm();

        // initialize, so we know about any autocomplete fields
        $webForm->initializeForm();

        /** @var Autocomplete $field */
        $field = $webForm->getField($fieldKey);

        /** @var Model $model */
        $model = $field->getSourceModel();

        return json_encode($model::getNameList());
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
     * @return WebForm
     * @throws InvalidArgumentException
     */
    private function getWebForm()
    {
        $class = $this->request->getPost(WebForm::WEB_FORM_CLASS);

        /** @var WebForm $webForm */
        $webForm = new $class();

        if ( ! $webForm instanceof WebForm) {
            throw new InvalidArgumentException();
        }

        return $webForm;
    }
}