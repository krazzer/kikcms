<?php

namespace KikCMS\Classes\Finder;


use Exception;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Config\MimeConfig;
use KikCMS\Models\FinderFile;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Finder\FinderPermissionService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\UserService;
use Phalcon\Http\Request\File;

/**
 * @property FinderService $finderService
 * @property FinderFileService $finderFileService
 * @property FinderPermissionService $finderPermissionService
 * @property Translator $translator
 * @property AccessControl $acl
 * @property UserService $userService
 * @property CmsService $cmsService
 */
class Finder extends Renderable
{
    const JS_TRANSLATIONS = [
        'media.deleteConfirm',
        'media.deleteConfirmOne',
        'media.createFolder',
        'media.defaultFolderName',
        'media.editFileName',
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
        $this->view->jsTranslations = array_merge($this->view->jsTranslations, self::JS_TRANSLATIONS);
    }

    /**
     * @return bool
     */
    public function allowedInCurrentFolder(): bool
    {
        if ( ! $this->acl->allowedFinder()) {
            return false;
        }

        return $this->userService->allowedInFolderId($this->getFilters()->getFolderId());
    }

    /**
     * @return FinderFilters|Filters
     */
    public function getFinderFilters(): FinderFilters
    {
        return parent::getFilters();
    }

    /**
     * @return FinderFilters|Filters
     */
    public function getFilters(): Filters
    {
        if ( ! $this->getFinderFilters()->getFolderId()) {
            $this->finderService->setStartingFolder($this->getFinderFilters());
        }

        return $this->getFinderFilters();
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if ( ! $this->allowedInCurrentFolder()) {
            throw new UnauthorizedException();
        }

        $this->addAssets();

        $files = $this->finderFileService->getByFilters($this->getFilters());

        return $this->renderView('index', [
            'files'       => $files,
            'instance'    => $this->getInstance(),
            'jsData'      => $this->getJsData(),
            'path'        => $this->renderPath(),
            'pickingMode' => $this->pickingMode,
            'permission'  => $this->config->media->manageFilePermissions,
            'roleMap'     => $this->cmsService->getRoleMap(false),
            'visitorRole' => Permission::VISITOR,
            'userMap'     => $this->finderPermissionService->getEditableUserMap(),
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
     * @param int|null $overwriteFileId
     * @return UploadStatus
     */
    public function uploadFiles(array $files, int $overwriteFileId = null): UploadStatus
    {
        $uploadStatus = new UploadStatus();

        if ($overwriteFileId && count($files) !== 1) {
            throw new Exception('When overwriting, only 1 file is allowed to upload');
        }

        foreach ($files as $index => $file) {

            if ($file->getError()) {
                $message = $this->translator->tl('media.upload.error.failed', ['fileName' => $file->getName()]);
                $uploadStatus->addError($message);
                continue;
            }

            if ( ! $this->mimeTypeAllowed($file)) {
                $message = $this->translator->tl('media.upload.error.mime', [
                    'extension' => $file->getExtension(),
                    'fileName'  => $file->getName()
                ]);
                $uploadStatus->addError($message);
                continue;
            }

            if ($overwriteFileId) {
                if ($this->finderFileService->overwrite($file, $overwriteFileId)) {
                    $newFileId = $overwriteFileId;
                } else {
                    $newFileId = false;
                }
            } else {
                $newFileId = $this->finderFileService->create($file, $this->getFilters()->getFolderId());
            }

            if ( ! $newFileId) {
                $message = $this->translator->tl('media.upload.error.failed', ['fileName' => $file->getName()]);
                $uploadStatus->addError($message);
                continue;
            }

            $uploadStatus->addFileId($newFileId);
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