<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Config\MimeConfig;
use KikCMS\Models\FinderFile;
use Phalcon\Http\Request\File;

/**
 * @property FinderFileService $finderFileService
 * @property Translator $translator
 */
class Finder extends Renderable
{
    const JS_TRANSLATIONS = [
        'media.deleteConfirm',
        'media.deleteConfirmOne',
        'media.createFolder',
        'media.defaultFolderName',
        'media.editFileName',
        'media.uploadMaxFilesWarning',
        'media.uploadMaxFileSizeWarning',
    ];

    /** @inheritdoc */
    protected $viewDirectory = 'finder';

    /** @inheritdoc */
    protected $jsClass = 'Finder';

    /** inheritdoc */
    protected $instancePrefix = 'Finder';

    /** @var bool */
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
     * @return FinderFilters|Filters
     */
    public function getFilters(): Filters
    {
        return parent::getFilters();
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $this->addAssets();

        $files = $this->finderFileService->getByFilters($this->getFilters());

        return $this->renderView('index', [
            'files'       => $files,
            'instance'    => $this->getInstance(),
            'jsData'      => $this->getJsData(),
            'pickingMode' => $this->pickingMode,
        ]);
    }

    /**
     * @return string
     */
    public function renderFiles()
    {
        $files = $this->finderFileService->getByFilters($this->getFilters());

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
     * @return string
     */
    public function renderPath()
    {
        $folderId = $this->getFilters()->getFolderId();

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
     * @param bool $pickingMode
     */
    public function setPickingMode(bool $pickingMode)
    {
        $this->pickingMode = $pickingMode;
    }

    /**
     * @param File[] $files
     *
     * @return UploadStatus
     */
    public function uploadFiles(array $files): UploadStatus
    {
        $uploadStatus = new UploadStatus();

        foreach ($files as $index => $file) {

            if ($file->getError()) {
                $message = $this->translator->tlb('media.upload.error.failed', ['fileName' => $file->getName()]);
                $uploadStatus->addError($message);
                continue;
            }

            if ( ! $this->mimeTypeAllowed($file)) {
                $message = $this->translator->tlb('media.upload.error.mime', [
                    'extension' => $file->getExtension(),
                    'fileName'  => $file->getName()
                ]);
                $uploadStatus->addError($message);
                continue;
            }

            $result = $this->finderFileService->create($file, $this->getFilters()->getFolderId());

            if ( ! $result) {
                $message = $this->translator->tlb('media.upload.error.failed', ['fileName' => $file->getName()]);
                $uploadStatus->addError($message);
                continue;
            }

            $uploadStatus->addFileId($result);
        }

        return $uploadStatus;
    }

    /**
     * @return Filters|FinderFilters
     */
    public function getEmptyFilters(): Filters
    {
        return new FinderFilters();
    }

    /**
     * This method may contain logic that will influence the output when rendered
     */
    protected function initialize()
    {
    }

    /**
     * @inheritdoc
     */
    protected function getJsProperties(): array
    {
        return ['pickingMode' => $this->pickingMode];
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