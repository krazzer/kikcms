<?php declare(strict_types=1);

namespace KikCMS\Classes\Finder;


use Exception;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Config\MimeConfig;
use KikCMS\Models\File;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Finder\FileService;
use KikCMS\Services\Finder\FilePermissionService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\UserService;
use Phalcon\Http\Request\File as UploadedFile;

/**
 * @property FinderService $finderService
 * @property FileService $fileService
 * @property FilePermissionService $filePermissionService
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

    /** @var bool */
    private $multiPick = false;

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
        if ( ! $folderId = $this->getFilters()->getFolderId()) {
            return true;
        }

        return $this->filePermissionService->canReadId($folderId);
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

        $files = $this->fileService->getByFilters($this->getFilters());

        return $this->renderView('index', [
            'files'       => $files,
            'instance'    => $this->getInstance(),
            'jsData'      => $this->getJsData(),
            'path'        => $this->renderPath(),
            'pickingMode' => $this->pickingMode,
            'permission'  => $this->filePermissionService->isEnabled(),
            'roleMap'     => $this->cmsService->getRoleMap(false),
            'visitorRole' => Permission::VISITOR,
            'userMap'     => $this->filePermissionService->getEditableUserMap(),
        ]);
    }

    /**
     * @return string
     */
    public function renderFiles()
    {
        $files = $this->fileService->getByFilters($this->getFilters());

        return $this->renderView('files', [
            'files' => $files,
        ]);
    }

    /**
     * @param File $file
     * @return string
     */
    public function renderFilePreview(File $file)
    {
        return $this->renderView('file', [
            'file' => $file,
        ]);
    }

    /**
     * @return string
     */
    public function renderPath()
    {
        $folderId = $this->getFilters()->getFolderId();

        $path = $this->fileService->getFolderPath($folderId);
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
     * @param UploadedFile[] $files
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
                if ($this->fileService->overwrite($file, $overwriteFileId)) {
                    $newFileId = $overwriteFileId;
                } else {
                    $newFileId = false;
                }
            } else {
                $newFileId = $this->fileService->create($file, $this->getFilters()->getFolderId());
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
     * @param UploadedFile $uploadedFile
     * @return bool
     */
    public function mimeTypeAllowed(UploadedFile $uploadedFile): bool
    {
        $allowedMimes = MimeConfig::UPLOAD_ALLOW_DEFAULT;
        $fileMimeType = $uploadedFile->getRealType();
        $extension    = $uploadedFile->getExtension();
        $extension    = strtolower($extension);

        // check if the extension is allowed
        if ( ! in_array($extension, $allowedMimes)) {
            return false;
        }

        // check if the file's mime matches it's extension
        return in_array($fileMimeType, MimeConfig::ALL_MIME_TYPES[$extension]);
    }

    /**
     * @return bool
     */
    public function isMultiPick(): bool
    {
        return $this->multiPick;
    }

    /**
     * @param bool $multiPick
     */
    public function setMultiPick(bool $multiPick): void
    {
        $this->multiPick = $multiPick;
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
        return [
            'pickingMode' => $this->pickingMode,
            'multiPick'   => $this->isMultiPick(),
        ];
    }
}