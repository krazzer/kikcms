<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Translator;
use KikCMS\Config\MimeConfig;
use KikCMS\Models\FinderFile;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;

/**
 * @property FinderFileService $finderFileService
 * @property Translator $translator
 */
class Finder extends Injectable
{
    const JS_TRANSLATIONS = [
        'media.deleteConfirm',
        'media.deleteConfirmOne',
        'media.createFolder',
        'media.defaultFolderName',
        'media.editFileName',
        'media.uploadMaxFilesWarning',
    ];

    private $pickingMode = false;

    /**
     * Adds html/css required for finder
     */
    public function addAssets()
    {
        $this->view->assets->addCss('cmsassets/css/toolbarComponent.css');
        $this->view->assets->addCss('cmsassets/css/finder.css');
        $this->view->assets->addJs('cmsassets/js/finder/finder.js');
        $this->view->assets->addJs('cmsassets/js/finder/uploader.js');

        $this->view->jsTranslations = array_merge($this->view->jsTranslations, self::JS_TRANSLATIONS);
    }

    /**
     * @param array $filters
     * @return string
     */
    public function render($filters = [])
    {
        $this->addAssets();

        $files = $this->finderFileService->getByFilters($filters);

        return $this->renderView('index', [
            'files'          => $files,
            'instance'       => $this->getInstance(),
            'pickingMode'    => $this->pickingMode,
            'maxFileUploads' => $this->getMaxFileUploads(),
            'isAjax'         => $this->request->isAjax(),
        ]);
    }

    /**
     * @param array $filters
     * @return string
     */
    public function renderFiles($filters = [])
    {
        $files = $this->finderFileService->getByFilters($filters);

        return $this->renderView('files', [
            'files' => $files,
        ]);
    }

    /**
     * @param FinderFile $finderFile
     * @return string
     */
    public function renderFilePreview(FinderFile $finderFile)
    {
        return $this->renderView('file', [
            'finderFile' => $finderFile,
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
     * @param bool $pickingMode
     */
    public function setPickingMode(bool $pickingMode)
    {
        $this->pickingMode = $pickingMode;
    }

    /**
     * @param File[] $files
     * @param int $folderId
     *
     * @return UploadStatus
     */
    public function uploadFiles(array $files, $folderId = 0): UploadStatus
    {
        $uploadStatus = new UploadStatus();

        foreach ($files as $index => $file) {
            if ( ! $this->mimeTypeAllowed($file)) {
                $message = $this->translator->tl('media.upload.error.mime', [
                    'extension' => $file->getExtension(),
                    'fileName'  => $file->getName()
                ]);
                $uploadStatus->addError($message);
                continue;
            }

            $result = $this->finderFileService->create($file, $folderId);

            if ( ! $result) {
                $message = $this->translator->tl('media.upload.error.failed', ['fileName' => $file->getName()]);
                $uploadStatus->addError($message);
                continue;
            }

            $uploadStatus->addFileId($result);
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

    /**
     * @param File $file
     * @return bool
     */
    private function mimeTypeAllowed(File $file): bool
    {
        $allowedMimes = MimeConfig::UPLOAD_ALLOW_DEFAULT;
        $fileMimeType = $file->getRealType();
        $extension    = $file->getExtension();
        $extension    = strtolower($extension);

        // check if extension is known
        if ( ! array_key_exists($extension, MimeConfig::ALL_MIME_TYPES)) {
            return false;
        }

        // check if the extension is allowed
        if ( ! in_array($extension, $allowedMimes)) {
            return false;
        }

        // check if the file's mime matches it's extension
        return in_array($fileMimeType, MimeConfig::ALL_MIME_TYPES[$extension]);
    }
}