<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Database\Now;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderPermission;
use KikCMS\Services\Finder\FinderPermissionService;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\ObjectStorage\FileStorage;
use KikCMS\Models\FinderFolder;
use KikCMS\Models\FinderFile;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Config;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset;

/**
 * Handles FinderFiles
 *
 * @property AccessControl $acl
 * @property ImageHandler $imageHandler
 * @property DbService $dbService
 * @property WebsiteService $websiteService
 * @property MediaResizeBase $mediaResize
 * @property UserService $userService
 * @property FinderPermissionService $finderPermissionService
 * @property Config $config
 */
class FinderFileService extends Injectable
{
    /** @var FileStorage */
    private $fileStorage;

    /** @var string */
    private $mediaDir;

    /** @var string */
    private $thumbDir;

    /**
     * @param FileStorage $fileStorage
     */
    public function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param File $file
     * @param int $folderId
     * @return bool|int
     */
    public function create(File $file, $folderId = 0)
    {
        $mimeType = $file->getRealType();

        $finderFile = new FinderFile();

        $finderFile->name      = $file->getName();
        $finderFile->extension = $file->getExtension();
        $finderFile->size      = $file->getSize();
        $finderFile->created   = new Now();
        $finderFile->updated   = new Now();
        $finderFile->mimetype  = $mimeType;
        $finderFile->folder_id = $folderId;
        $finderFile->is_folder = 0;

        if ( ! $finderFile->save()) {
            return false;
        }

        $this->finderPermissionService->createForFile($finderFile);

        $this->fileStorage->storeByRequest($file, $this->mediaDir, $finderFile->id);
        $this->resizeWithinBoundaries($finderFile);

        return (int) $finderFile->id;
    }

    /**
     * @param string $folderName
     * @param int $folderId
     *
     * @return int
     */
    public function createFolder(string $folderName, $folderId = 0): int
    {
        $finderDir            = new FinderFolder();
        $finderDir->name      = $folderName;
        $finderDir->folder_id = $folderId;
        $finderDir->is_folder = 1;

        $finderDir->save();

        $this->finderPermissionService->createForFile($finderDir);

        return (int) $finderDir->id;
    }

    /**
     * @param FinderFile $finderFile
     * @param string|null $type
     */
    public function createThumb(FinderFile $finderFile, string $type = null)
    {
        $filePath  = $this->getFilePath($finderFile);
        $thumbPath = $this->getThumbPath($finderFile, $type);

        $image = $this->imageHandler->create($filePath);

        if ($type == null) {
            $image->resize(192, 192);
        } else {
            $this->mediaResize->resizeByType($image, $type);
        }

        $image->save($thumbPath, 90);
    }

    /**
     * @param int[] $fileIds
     */
    public function deleteFilesByIds(array $fileIds)
    {
        $finderFiles = FinderFile::getByIdList($fileIds);

        // get sub files
        foreach ($finderFiles as $file) {
            $fileIds = $this->getFileIdsRecursive($file, $fileIds);
        }

        $finderFiles  = FinderFile::getByIdList($fileIds);
        $filesRemoved = $this->dbService->delete(FinderFile::class, ['id' => $fileIds]);

        if ($filesRemoved) {
            foreach ($finderFiles as $finderFile) {
                if ( ! $finderFile->isFolder()) {
                    $this->unlinkFiles($finderFile);
                }
            }
        }
    }

    /**
     * @param int $folderId
     * @return FinderFile[]
     */
    public function getByFolderId(int $folderId = 0)
    {
        $resultSet = FinderFile::find([
            FinderFile::FIELD_FOLDER_ID . ' = ' . $folderId,
            'order' => 'is_folder DESC, name ASC'
        ]);

        return $this->getFiles($resultSet);
    }

    /**
     * @param FinderFilters $filters
     * @return FinderFile[]|Resultset
     */
    public function getByFilters(FinderFilters $filters)
    {
        if ( ! $filters->getSearch()) {
            return $this->getByFolderId($filters->getFolderId());
        }

        $query = (new Builder)
            ->from(['f' => FinderFile::class])
            ->where(FinderFile::FIELD_NAME . ' LIKE :search:', ['search' => '%' . $filters->getSearch() . '%'])
            ->orderBy('is_folder DESC, name ASC');

        if($this->finderPermissionService->isEnabled()){
            $userId = $this->userService->getUserId();
            $role   = $this->userService->getRole();

            $query
                ->join(FinderPermission::class, 'fp.file_id = f.id', 'fp')
                ->andWhere('[' . FinderPermission::FIELD_RIGHT . '] >= :right:', ['right' => FinderConfig::RIGHT_READ])
                ->andWhere('fp.user_id = :userId: OR role = :role:', ['userId' => $userId, 'role' => $role]);
        }

        return $this->dbService->getObjects($query);
    }

    /**
     * @param string $key
     * @return FinderFile|null
     */
    public function getByKey(string $key): ?FinderFile
    {
        $query = (new Builder)
            ->from(FinderFile::class)
            ->inWhere(FinderFile::FIELD_KEY, [$key]);

        return $this->dbService->getObject($query);
    }

    /**
     * @param FinderFile $finderFile
     *
     * @return string
     */
    public function getFilePath(FinderFile $finderFile)
    {
        $fileName = $finderFile->id . '.' . $finderFile->getExtension();

        return $this->fileStorage->getStorageDir() . $this->getMediaDir() . '/' . $fileName;
    }

    /**
     * @param int $folderId
     * @param array $path
     *
     * @return array
     */
    public function getFolderPath(int $folderId, $path = [])
    {
        $homeFolderId = $this->getHomeFolderId();

        if ($folderId == $homeFolderId) {
            $path[$homeFolderId] = "Home";
            return $path;
        }

        $folder = FinderFolder::getById($folderId);

        $path[$folderId] = $folder->name;

        return $this->getFolderPath($folder->getFolderId(), $path);
    }

    /**
     * @param FinderFile $finderFile
     * @param string|null $type
     *
     * @return string
     */
    public function getThumbPath(FinderFile $finderFile, string $type = null)
    {
        $type     = $type ?: 'default';
        $fileName = $finderFile->id . '.' . $finderFile->getExtension();
        $dirPath  = $this->fileStorage->getStorageDir() . $this->getThumbDir() . '/' . $type . '/';

        if ( ! file_exists($dirPath)) {
            mkdir($dirPath);
        }

        return $dirPath . $fileName;
    }

    /**
     * @return string
     */
    public function getMediaDir(): string
    {
        return $this->mediaDir;
    }

    /**
     * @param string $mediaDir
     */
    public function setMediaDir(string $mediaDir)
    {
        $this->mediaDir = $mediaDir;
    }

    /**
     * @return string
     */
    public function getThumbDir(): string
    {
        return $this->thumbDir;
    }

    /**
     * @param int[] $fileIds
     * @param int $folderId
     */
    public function moveFilesToFolderById(array $fileIds, int $folderId)
    {
        $fileIds = $this->removeFileIdsInPath($fileIds, $folderId);

        $this->dbService->update(FinderFile::class, [
            FinderFile::FIELD_FOLDER_ID => $folderId
        ], [
            FinderFile::FIELD_ID => $fileIds
        ]);
    }

    /**
     * @param string $thumbDir
     */
    public function setThumbDir(string $thumbDir)
    {
        $this->thumbDir = $thumbDir;
    }

    /**
     * @param int $fileId
     * @param string $fileName
     */
    public function updateFileNameById(int $fileId, string $fileName)
    {
        $finderFile = FinderFile::getById($fileId);

        if ($finderFile->isFolder()) {
            $finderFile->name = $fileName;
        } else {
            $finderFile->name = $fileName . '.' . $finderFile->extension;
        }

        $finderFile->save();
    }

    /**
     * Gets all sub file id's recursively
     *
     * @param FinderFile $file
     * @param array $fileIds
     *
     * @return array
     */
    public function getFileIdsRecursive(FinderFile $file, $fileIds = []): array
    {
        if ( ! $file->isFolder()) {
            $fileIds[] = $file->getId();
            return $fileIds;
        }

        $finderFiles = $this->getByFolderId($file->getId());

        foreach ($finderFiles as $subFile) {
            $fileIds = $this->getFileIdsRecursive($subFile, $fileIds);
        }

        // add file id itself
        $fileIds[] = $file->getId();

        return $fileIds;
    }

    /**
     * @param FinderFile $finderFile
     * @return array|null returns same results as @see getimagesize
     */
    public function getImageDimensions(FinderFile $finderFile)
    {
        if ( ! $finderFile->isImage()) {
            return null;
        }

        $filePath = $this->getFilePath($finderFile);

        if ( ! file_exists($filePath)) {
            return null;
        }

        return getimagesize($filePath);
    }

    /**
     * @param FinderFile $finderFile
     * @return array|null returns same results as @see getimagesize
     */
    public function getThumbDimensions(FinderFile $finderFile)
    {
        if ( ! $finderFile->isImage()) {
            return null;
        }

        $thumbPath = $this->getThumbPath($finderFile);

        if ( ! file_exists($thumbPath)) {
            $this->createThumb($finderFile);
        }

        return getimagesize($thumbPath);
    }

    /**
     * @param Resultset $resultSet
     * @return FinderFile[]
     */
    private function getFiles(Resultset $resultSet)
    {
        $files = [];

        foreach ($resultSet as $result) {
            $files[] = $result;
        }

        return $files;
    }

    /**
     * @param int[] $fileIds
     * @param int $folderId
     *
     * @return int[]
     */
    private function removeFileIdsInPath(array $fileIds, int $folderId)
    {
        $breadCrumbs = $this->getFolderPath($folderId);

        foreach ($fileIds as $i => $fileId) {
            if (array_key_exists($fileId, $breadCrumbs)) {
                unset($fileIds[$i]);
            }
        }

        return $fileIds;
    }

    /**
     * @param File $file
     * @param int $fileId
     * @return bool
     */
    public function overwrite(File $file, int $fileId): bool
    {
        $mimeType = $file->getRealType();

        $finderFile = FinderFile::getById($fileId);

        $finderFile->name      = $file->getName();
        $finderFile->extension = $file->getExtension();
        $finderFile->size      = $file->getSize();
        $finderFile->updated   = new Now();
        $finderFile->mimetype  = $mimeType;

        if ( ! $finderFile->save()) {
            return false;
        }

        $this->fileStorage->storeByRequest($file, $this->mediaDir, $finderFile->id);
        $this->resizeWithinBoundaries($finderFile);
        $this->removeThumbNails($finderFile);

        return true;
    }

    /**
     * @param FinderFile $finderFile
     */
    private function removeThumbNails(FinderFile $finderFile)
    {
        $thumbNailDirs = glob($this->fileStorage->getStorageDir() . $this->getThumbDir() . '/*');

        foreach ($thumbNailDirs as $thumbNailDir) {
            $thumbFile = $this->getThumbPath($finderFile, basename($thumbNailDir));

            if (file_exists($thumbFile)) {
                unlink($thumbFile);
            }
        }
    }

    /**
     * @param FinderFile $finderFile
     */
    private function resizeWithinBoundaries(FinderFile $finderFile)
    {
        // is no image, so do nothing
        if ( ! $finderFile->isImage()) {
            return;
        }

        $dimensions = $this->getImageDimensions($finderFile);
        $filePath   = $this->getFilePath($finderFile);

        $image      = $this->imageHandler->create($filePath);
        $jpgQuality = $this->config->media->jpgQuality;

        // could not fetch dimensions, so do nothing
        if ( ! $dimensions) {
            $image->save($filePath, $jpgQuality);
            return;
        }

        $maxWidth  = $this->config->media->maxWidth;
        $maxHeight = $this->config->media->maxHeight;

        // smaller than required, so do nothing
        if ($dimensions[0] <= $maxWidth && $dimensions[1] <= $maxHeight) {
            $image->save($filePath, $jpgQuality);
            return;
        }

        // resize
        $image = $this->imageHandler->create($filePath);
        $image->resize($maxWidth, $maxHeight);
        $image->save($filePath, $jpgQuality);
    }

    /**
     * Remove all files and thumb files for the given FinderFile
     *
     * @param FinderFile $finderFile
     */
    private function unlinkFiles(FinderFile $finderFile)
    {
        unlink($this->getFilePath($finderFile));
        $this->removeThumbNails($finderFile);
    }

    /**
     * @return int
     */
    private function getHomeFolderId(): int
    {
        if ($this->acl->allowed(Permission::ACCESS_FINDER_FULL)) {
            return 0;
        }

        return $this->userService->getUser()->folder->getId();
    }
}