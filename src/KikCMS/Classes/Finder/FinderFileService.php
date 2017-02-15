<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Database\Now;
use KikCMS\Classes\DbService;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Storage\FileStorage;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderFolder;
use KikCMS\Models\FinderFile;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;
use Phalcon\Mvc\Model\Resultset;

/**
 * Handles FinderFiles
 *
 * @property ImageHandler $imageHandler
 * @property DbService $dbService
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

        if ( ! $finderFile->save()) {
            return false;
        }

        $this->fileStorage->store($file, $this->mediaDir, $finderFile->id);

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

        $finderDir->save();

        return (int) $finderDir->id;
    }

    /**
     * @param FinderFile $finderFile
     */
    public function createThumb(FinderFile $finderFile)
    {
        $filePath  = $this->getFilePath($finderFile);
        $thumbPath = $this->getThumbPath($finderFile);

        $image = $this->imageHandler->create($filePath);
        $image->resize(192, 192);
        $image->save($thumbPath, 90);
    }

    /**
     * @param int[] $fileIds
     */
    public function deleteFilesByIds(array $fileIds)
    {
        $finderFiles  = FinderFile::getByIdList($fileIds);
        $filesRemoved = $this->dbService->delete(FinderFile::class, ['id' => $fileIds]);

        if ($filesRemoved) {
            foreach ($finderFiles as $finderFile) {
                unlink($this->getFilePath($finderFile));
                unlink($this->getThumbPath($finderFile));
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
     * @param array $filters
     * @return FinderFile[]
     */
    public function getByFilters(array $filters)
    {
        if (isset($filters[FinderConfig::FILTER_SEARCH])) {
            $resultSet = FinderFile::find([
                'conditions' => 'name LIKE {search}',
                'bind'       => ['search' => '%' . $filters[FinderConfig::FILTER_SEARCH] . '%'],
                'order'      => 'is_folder DESC, name ASC'
            ]);

            return $this->getFiles($resultSet);
        }

        if (isset($filters[FinderConfig::FILTER_FOLDER_ID])) {
            return $this->getByFolderId($filters[FinderConfig::FILTER_FOLDER_ID]);
        }

        return $this->getByFolderId();
    }

    /**
     * @param FinderFile $finderFile
     *
     * @return string
     */
    public function getFilePath(FinderFile $finderFile)
    {
        return $this->fileStorage->getStorageDir() . $this->getMediaDir() . '/' . $finderFile->id;
    }

    /**
     * @param int $folderId
     * @param array $path
     *
     * @return array
     */
    public function getFolderPath(int $folderId, $path = [])
    {
        if ($folderId == 0) {
            $path[0] = "Home";
            return $path;
        }

        $folder = FinderFolder::getById($folderId);

        $path[$folderId] = $folder->name;

        return $this->getFolderPath($folder->getFolderId(), $path);
    }

    /**
     * @param FinderFile $finderFile
     *
     * @return string
     */
    public function getThumbPath(FinderFile $finderFile)
    {
        $fileName = $finderFile->id . '.' . $finderFile->getExtension();

        return $this->fileStorage->getStorageDir() . $this->getThumbDir() . '/' . $fileName;
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
     * @param Resultset $resultSet
     * @return File[]
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
}