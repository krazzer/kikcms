<?php declare(strict_types=1);

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Models\File;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Finder\FileService;
use KikCMS\Services\Finder\FilePermissionService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\UserService;

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
    public function getFinderFilters(): FinderFilters|Filters
    {
        return parent::getFilters();
    }

    /**
     * @return FinderFilters|Filters
     */
    public function getFilters(): FinderFilters|Filters
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
            'isDev'       => $this->permission->isDev(),
        ]);
    }

    /**
     * @return string
     */
    public function renderFiles(): string
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
    public function renderFilePreview(File $file): string
    {
        return $this->renderView('file', [
            'file' => $file,
        ]);
    }

    /**
     * @return string
     */
    public function renderPath(): string
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
    public function setPickingMode(bool $pickingMode): void
    {
        $this->pickingMode = $pickingMode;
    }

    /**
     * @return Filters|FinderFilters
     */
    public function getEmptyFilters(): Filters|FinderFilters
    {
        return new FinderFilters();
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