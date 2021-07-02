<?php declare(strict_types=1);

namespace KikCMS\Services\Finder;


use Exception;
use KikCMS\Classes\Database\Now;
use KikCMS\Classes\Finder\FinderFilters;
use KikCMS\Classes\Finder\UploadStatus;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\FinderConfig;
use KikCMS\Config\MimeConfig;
use KikCMS\Models\FilePermission;
use KikCMS\ObjectLists\FileMap;
use KikCMS\Models\Folder;
use KikCMS\Models\File;
use Phalcon\Http\Request\File as UploadedFile;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Handles Files
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
        $this->fileResizeService->resizeWithinBoundaries($file);
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
        if ($this->isAnimatedGif($filePath)) {
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
            ->orderBy('is_folder DESC, name ASC')
            ->limit(1000);

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
        return $this->getStorageDir() . $this->getMediaDir() . '/' . $file->getFileName();
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
        return $this->config->application->path . $this->config->application->publicFolder . '/' .
            FinderConfig::MEDIA_DIR . '/';
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
    public function getUrlCreateIfMissing(File $file, bool $private = false): string
    {
        $fileMediaPath = $this->getMediaFilePath($file, $private);

        // create dir if not existing (which is always the case for private files)
        if ( ! is_dir(dirname($fileMediaPath))) {
            mkdir(dirname($fileMediaPath));
        }

        // symlink to original (relative) if the file is missing
        if ( ! file_exists($fileMediaPath)) {
            symlink($this->getRelativePath($fileMediaPath, $this->getFilePath($file)), $fileMediaPath);
        }

        // add seconds between create and update to avoid browser cache
        if ($secondsUpdated = $file->secondsUpdated()) {
            return $this->getUrl($file, $private) . '?u=' . $secondsUpdated;
        }

        return $this->getUrl($file, $private);
    }

    /**
     * @param File $file
     * @param bool $private
     * @return string
     */
    public function getUrl(File $file, bool $private = false): string
    {
        if ( ! $private) {
            return $this->getMediaFilesUrl() . $this->getMediaFileName($file);
        }

        return $this->getMediaFilesUrl() . $file->getHash() . DIRECTORY_SEPARATOR . $this->getMediaFileName($file);
    }

    /**
     * @param File $file
     * @param string|null $type
     * @param bool $private
     * @return string
     */
    public function getMediaThumbPath(File $file, string $type = FinderConfig::DEFAULT_THUMB_TYPE,
                                      bool $private = false): string
    {
        $dirPath = $this->getMediaThumbDir() . $type . DIRECTORY_SEPARATOR;

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
        $filesDir = $this->getMediaStorageDir() . FinderConfig::FILES_DIR . DIRECTORY_SEPARATOR;

        if ( ! $private) {
            return $filesDir . $this->getMediaFileName($file);
        }

        return $filesDir . $file->getHash() . DIRECTORY_SEPARATOR . $this->getMediaFileName($file);
    }

    /**
     * @return string
     */
    public function getMediaThumbDir(): string
    {
        return $this->getMediaStorageDir() . FinderConfig::THUMB_DIR . DIRECTORY_SEPARATOR;
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
        $this->fileResizeService->resizeWithinBoundaries($file);
        $this->fileRemoveService->removeThumbNails($file);
        $this->fileCacheService->removeUrlCache($file);
        $this->fileHashService->updateHash($file);

        return true;
    }

    /**
     * @param File $file
     * @param string $type
     * @param bool $private
     * @return string
     */
    public function getThumbUrl(File $file, string $type, bool $private = false): string
    {
        return $this->getMediaThumbsUrl() . $type . DIRECTORY_SEPARATOR . $file->getFileName($private);
    }

    /**
     * Get the url for a thumbnail, and create it if it doesn't exist
     *
     * @param File $file
     * @param string $type
     * @param bool $private
     * @return string
     */
    public function getThumbUrlCreateIfMissing(File $file, string $type, bool $private = false): string
    {
        $thumbFilePath = $this->getMediaThumbPath($file, $type, $private);

        // svg's don't need thumbs, just return the URL
        if ($file->getExtension() == MimeConfig::SVG) {
            return $this->getUrlCreateIfMissing($file, $private);
        }

        if ( ! file_exists($thumbFilePath)) {
            $this->createMediaThumb($file, $type, $private);
        }

        $url = $this->getThumbUrl($file, $type, $private);

        // add seconds between create and update to avoid browser cache
        if ($secondsUpdated = $file->secondsUpdated()) {
            return $url . '?u=' . $secondsUpdated;
        }

        return $url;
    }

    /**
     * @param File $file
     * @return string
     */
    public function getUrlFriendlyFileName(File $file): string
    {
        $fileNameParts = explode('.', $file->getName());

        if (count($fileNameParts) === 1) {
            return $this->urlService->toSlug($file->getName());
        }

        $extension = array_pop($fileNameParts);

        return $this->urlService->toSlug(implode('.', $fileNameParts)) . '.' . $extension;
    }

    /**
     * @param string $from
     * @param string $to
     * @return string
     */
    public function getRelativePath(string $from, string $to): string
    {
        $from    = explode('/', $from);
        $to      = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath   = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }

        return implode('/', $relPath);
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
     * @param UploadedFile[] $files
     * @param int|null $folderId
     * @param int|null $overwriteFileId
     * @return UploadStatus
     */
    public function uploadFiles(array $files, int $folderId = null, int $overwriteFileId = null): UploadStatus
    {
        $uploadStatus = new UploadStatus();

        if ($overwriteFileId && count($files) !== 1) {
            throw new Exception('When overwriting, only 1 file is allowed to upload');
        }

        foreach ($files as $file) {
            if ($file->getError()) {
                $message = $this->translator->tl('media.upload.error.failed', ['fileName' => $file->getName()]);
                $uploadStatus->addError($message);
                continue;
            }

            if (strlen($file->getName()) > FinderConfig::MAX_FILENAME_LENGTH) {
                $message = $this->translator->tl('media.upload.error.nameLength', [
                    'max'      => FinderConfig::MAX_FILENAME_LENGTH,
                    'fileName' => substr($file->getName(), 0, 50) . '...',
                ]);

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
                $newFileId = $this->fileService->create($file, $folderId);
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

    /**
     * @param File $file
     * @return string
     */
    private function getMediaFileName(File $file): string
    {
        return $file->getId() . '-' . $this->getUrlFriendlyFileName($file);
    }
}