<?php

namespace KikCMS\Services\Finder;


use ImagickException;
use KikCMS\Classes\Database\Now;
use KikCMS\Classes\Finder\FinderFilters;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Config\FinderConfig;
use KikCMS\Config\MimeConfig;
use KikCMS\Models\FilePermission;
use KikCMS\ObjectLists\FileMap;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\ObjectStorage\FileStorage;
use KikCMS\Models\Folder;
use KikCMS\Models\File;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Config;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File as UploadedFile;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Handles Files
 *
 * @property AccessControl $acl
 * @property ImageHandler $imageHandler
 * @property DbService $dbService
 * @property WebsiteService $websiteService
 * @property MediaResizeBase $mediaResize
 * @property UserService $userService
 * @property FileStorage $fileStorage
 * @property FileHashService $fileHashService
 * @property FilePermissionService $filePermissionService
 * @property FileRemoveService $fileRemoveService
 * @property FileCacheService $fileCacheService
 * @property Config $config
 */
class FileService extends Injectable
{
    /** @var string */
    private $mediaDir;

    /** @var string */
    private $thumbDir;

    /**
     * @param string $mediaDir
     * @param string $thumbDir
     */
    public function __construct(string $mediaDir, string $thumbDir)
    {
        $this->mediaDir = $mediaDir;
        $this->thumbDir = $thumbDir;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param int|null $folderId
     * @return bool|int
     */
    public function create(UploadedFile $uploadedFile, $folderId = null)
    {
        $mimeType = $uploadedFile->getRealType();

        $file = new File();

        $file->name      = $uploadedFile->getName();
        $file->size      = $uploadedFile->getSize();
        $file->extension = strtolower($uploadedFile->getExtension());
        $file->created   = new Now();
        $file->updated   = new Now();
        $file->mimetype  = $mimeType;
        $file->folder_id = $folderId;
        $file->is_folder = 0;

        if ( ! $file->save()) {
            return false;
        }

        $this->filePermissionService->createForFile($file);

        $this->fileStorage->storeByRequest($uploadedFile, $this->mediaDir, $file->id);
        $this->resizeWithinBoundaries($file);
        $this->fileHashService->updateHash($file);

        return (int) $file->id;
    }

    /**
     * @param string $folderName
     * @param int|null $folderId
     *
     * @return int
     */
    public function createFolder(string $folderName, $folderId = null): int
    {
        $folder            = new Folder();
        $folder->name      = $folderName;
        $folder->folder_id = $folderId;
        $folder->is_folder = 1;

        $folder->save();

        $this->filePermissionService->createForFile($folder);

        return (int) $folder->id;
    }

    /**
     * @param File $file
     * @param string|null $type
     * @param bool $private
     */
    public function createMediaThumb(File $file, string $type = FinderConfig::DEFAULT_THUMB_TYPE, bool $private = false)
    {
        $filePath  = $this->getFilePath($file);
        $thumbPath = $this->getMediaThumbPath($file, $type, $private);

        // do not resize animated gifs
        if($this->isAnimatedGif($filePath)){
            copy($filePath, $thumbPath);
            return;
        }

        $image = $this->imageHandler->create($filePath);

        $this->mediaResize->resizeByType($image, $type);

        $image->save($thumbPath, 90);
    }

    /**
     * @param int|null $folderId
     * @return File[]
     */
    public function getByFolderId(int $folderId = null): array
    {
        $query = (new Builder)
            ->from(File::class)
            ->orderBy(File::FIELD_IS_FOLDER . ' DESC, ' . File::FIELD_NAME)
            ->where(File::FIELD_FOLDER_ID . ($folderId ? ' = ' . $folderId : ' IS NULL'));

        return $this->dbService->getObjects($query);
    }

    /**
     * @param FinderFilters $filters
     * @return File[]
     */
    public function getByFilters(FinderFilters $filters): array
    {
        if ( ! $filters->getSearch()) {
            return $this->getByFolderId($filters->getFolderId());
        }

        $query = (new Builder)
            ->from(['f' => File::class])
            ->where(File::FIELD_NAME . ' LIKE :search:', ['search' => '%' . $filters->getSearch() . '%'])
            ->orderBy('is_folder DESC, name ASC');

        if ($this->filePermissionService->isEnabled()) {
            $userId = $this->userService->getUserId();
            $role   = $this->userService->getRole();

            $query
                ->join(FilePermission::class, 'fp.file_id = f.id', 'fp')
                ->andWhere('[' . FilePermission::FIELD_RIGHT . '] >= :right:', ['right' => FinderConfig::RIGHT_READ])
                ->andWhere('fp.user_id = :userId: OR role = :role:', ['userId' => $userId, 'role' => $role]);
        }

        return $this->dbService->getObjects($query);
    }

    /**
     * @param array $idList
     * @return FileMap
     */
    public function getByIdList(array $idList): FileMap
    {
        $query = (new Builder)
            ->from(File::class)
            ->inWhere(File::FIELD_ID, $idList);

        return $this->dbService->getObjectMap($query, FileMap::class);
    }

    /**
     * @param string $key
     * @return File|null
     */
    public function getByKey(string $key): ?File
    {
        $query = (new Builder)
            ->from(File::class)
            ->inWhere(File::FIELD_KEY, [$key]);

        return $this->dbService->getObject($query);
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public function getFilePath(File $file)
    {
        $fileName = $file->id . '.' . $file->getExtension();

        return $this->getStorageDir() . $this->getMediaDir() . '/' . $fileName;
    }

    /**
     * @param int|null $folderId
     * @param array $path
     *
     * @return array
     */
    public function getFolderPath(?int $folderId, $path = [])
    {
        $homeFolderId = $this->getHomeFolderId();

        if ($folderId == $homeFolderId) {
            $path[$homeFolderId] = "Home";
            return $path;
        }

        $folder = Folder::getById($folderId);

        $path[$folderId] = $folder->name;

        return $this->getFolderPath($folder->getFolderId(), $path);
    }

    /**
     * @return string
     */
    public function getStorageDir(): string
    {
        return $this->fileStorage->getStorageDir();
    }

    /**
     * @return string
     */
    public function getMediaStorageDir(): string
    {
        return SITE_PATH . $this->config->application->publicFolder . '/' . FinderConfig::MEDIA_DIR . '/';
    }

    /**
     * @return string
     */
    public function getMediaThumbsUrl(): string
    {
        return $this->url->get(FinderConfig::MEDIA_DIR . '/' . FinderConfig::THUMB_DIR . '/');
    }

    /**
     * @return string
     */
    public function getMediaFilesUrl(): string
    {
        return $this->url->get(FinderConfig::MEDIA_DIR . '/' . FinderConfig::FILES_DIR . '/');
    }

    /**
     * @param File $file
     * @param bool $private
     * @return string
     */
    public function getUrl(File $file, bool $private = false): string
    {
        $fileMediaPath = $this->getMediaFilePath($file, $private);

        if ( ! file_exists($fileMediaPath)) {
            symlink($this->getFilePath($file), $fileMediaPath);
        }

        $url = $this->getMediaFilesUrl() . $file->getFileName($private);

        // add seconds between create and update to avoid browser cache
        if($secondsUpdated = $file->secondsUpdated()){
            return $url . '?u=' . $secondsUpdated;
        }

        return $url;
    }

    /**
     * @param File $file
     * @param string|null $type
     * @param bool $private
     * @return string
     */
    public function getMediaThumbPath(File $file, string $type = FinderConfig::DEFAULT_THUMB_TYPE, bool $private = false): string
    {
        $dirPath = $this->getMediaThumbDir() . $type . '/';

        if ( ! file_exists($dirPath)) {
            mkdir($dirPath);
        }

        return $dirPath . $file->getFileName($private);
    }

    /**
     * @param File $file
     * @param bool $private
     * @return string
     */
    public function getMediaFilePath(File $file, bool $private = false): string
    {
        return $this->getMediaStorageDir() . FinderConfig::FILES_DIR . '/' . $file->getFileName($private);
    }

    /**
     * @return string
     */
    public function getMediaThumbDir(): string
    {
        return $this->getMediaStorageDir() . FinderConfig::THUMB_DIR . '/';
    }

    /**
     * @return string
     */
    public function getMediaDir(): string
    {
        return $this->mediaDir;
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
     * @param int|null $folderId
     */
    public function moveFilesToFolderById(array $fileIds, ?int $folderId)
    {
        $fileIds = $this->removeFileIdsInPath($fileIds, $folderId);

        $this->dbService->update(File::class, [
            File::FIELD_FOLDER_ID => $folderId
        ], [
            File::FIELD_ID => $fileIds
        ]);
    }

    /**
     * @param int $fileId
     * @param string $fileName
     */
    public function updateFileNameById(int $fileId, string $fileName)
    {
        $file = File::getById($fileId);

        if ($file->isFolder()) {
            $file->name = $fileName;
        } else {
            $file->name = $fileName . '.' . $file->extension;
        }

        $file->save();
    }

    /**
     * Gets all sub file id's recursively
     *
     * @param File $file
     * @param array $fileIds
     *
     * @return array
     */
    public function getFileIdsRecursive(File $file, $fileIds = []): array
    {
        if ( ! $file->isFolder()) {
            $fileIds[] = $file->getId();
            return $fileIds;
        }

        $files = $this->getByFolderId($file->getId());

        foreach ($files as $subFile) {
            $fileIds = $this->getFileIdsRecursive($subFile, $fileIds);
        }

        // add file id itself
        $fileIds[] = $file->getId();

        return $fileIds;
    }

    /**
     * @param File $file
     * @return array|null returns same results as @see getimagesize
     */
    public function getImageDimensions(File $file)
    {
        if ( ! $file->isImage()) {
            return null;
        }

        $filePath = $this->getFilePath($file);

        if ( ! file_exists($filePath)) {
            return null;
        }

        return getimagesize($filePath);
    }

    /**
     * @param File $file
     * @return array|null returns same results as @see getimagesize
     */
    public function getThumbDimensions(File $file)
    {
        if ( ! $file->isImage()) {
            return null;
        }

        $thumbPath = $this->getMediaThumbPath($file, FinderConfig::DEFAULT_THUMB_TYPE, true);

        if ( ! file_exists($thumbPath)) {
            $this->createMediaThumb($file, FinderConfig::DEFAULT_THUMB_TYPE, true);
        }

        return getimagesize($thumbPath);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function isAnimatedGif(string $filename): bool
    {
        if ( ! ($fh = @fopen($filename, 'rb'))) {
            return false;
        }

        $count = 0;

        while ( ! feof($fh) && $count < 2) {
            $chunk = fread($fh, 1024 * 100);
            $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $chunk, $matches);
        }

        fclose($fh);

        return $count > 1;
    }

    /**
     * @param int[] $fileIds
     * @param int|null $folderId
     *
     * @return int[]
     */
    private function removeFileIdsInPath(array $fileIds, ?int $folderId)
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
     * @param UploadedFile $uploadedFile
     * @param int $fileId
     * @return bool
     */
    public function overwrite(UploadedFile $uploadedFile, int $fileId): bool
    {
        $mimeType = $uploadedFile->getRealType();

        $file = File::getById($fileId);

        $file->name      = $uploadedFile->getName();
        $file->size      = $uploadedFile->getSize();
        $file->extension = strtolower($uploadedFile->getExtension());
        $file->updated   = new Now();
        $file->mimetype  = $mimeType;

        if ( ! $file->save()) {
            return false;
        }

        $this->fileStorage->storeByRequest($uploadedFile, $this->mediaDir, $file->id, true);
        $this->resizeWithinBoundaries($file);
        $this->fileRemoveService->removeThumbNails($file);
        $this->fileCacheService->removeUrlCache($file);
        $this->fileHashService->updateHash($file);

        return true;
    }

    /**
     * Get the url for a thumbnail, and create it if it doesn't exist
     *
     * @param File $file
     * @param string $type
     * @param bool $private
     * @return string
     */
    public function getThumbUrl(File $file, string $type, bool $private = false): string
    {
        $thumbFilePath = $this->getMediaThumbPath($file, $type, $private);

        // svg's don't need thumbs, just return the URL
        if($file->getExtension() == MimeConfig::SVG){
            return $this->getUrl($file, $private);
        }

        if ( ! file_exists($thumbFilePath)) {
            $this->createMediaThumb($file, $type, $private);
        }

        $url = $this->getMediaThumbsUrl() . $type . '/' . $file->getFileName($private);

        // add seconds between create and update to avoid browser cache
        if($secondsUpdated = $file->secondsUpdated()){
            return $url . '?u=' . $secondsUpdated;
        }

        return $url;
    }

    /**
     * @param File $file
     */
    private function resizeWithinBoundaries(File $file)
    {
        // is no image, so do nothing
        if ( ! $file->isImage()) {
            return;
        }

        $dimensions = $this->getImageDimensions($file);
        $filePath   = $this->getFilePath($file);

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
            try {
                $image->save($filePath, $jpgQuality);
            } catch (ImagickException $exception) {
                // suppress exception with code 410
                if ($exception->getCode() !== 410) {
                    throw $exception;
                }
            }

            return;
        }

        // resize
        $image = $this->imageHandler->create($filePath);
        $image->resize($maxWidth, $maxHeight);
        $image->save($filePath, $jpgQuality);
    }

    /**
     * @return int|null
     */
    private function getHomeFolderId(): ?int
    {
        if ( ! $this->filePermissionService->isEnabled()) {
            return null;
        }

        if ( ! $this->userService->getUser()->folder) {
            return null;
        }

        return $this->userService->getUser()->folder->getId();
    }
}