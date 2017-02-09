<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Database\Now;
use KikCMS\Classes\DbService;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Storage\FileStorage;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderDir;
use KikCMS\Models\FinderFile;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;
use Phalcon\Mvc\Model\Resultset;

/**
 * Handles FinderFiles
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

    const IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    /**
     * @param FileStorage $fileStorage
     */
    public function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param File $file
     * @param int $dirId
     * @return bool|int
     */
    public function create(File $file, $dirId = 0)
    {
        $finderFile = new FinderFile();

        $finderFile->name      = $file->getName();
        $finderFile->extension = $file->getExtension();
        $finderFile->mimetype  = $file->getRealType();
        $finderFile->size      = $file->getSize();
        $finderFile->created   = new Now();
        $finderFile->updated   = new Now();
        $finderFile->dir_id    = $dirId;

        if ( ! $finderFile->save()) {
            return false;
        }

        $this->fileStorage->store($file, $this->mediaDir, $finderFile->id);

        return (int) $finderFile->id;
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
     * @param FinderDir|null $finderDir
     * @return FinderFile[]
     */
    public function getByDir(FinderDir $finderDir = null)
    {
        $dirId     = $finderDir ? $finderDir->id : 0;
        $resultSet = FinderFile::find(FinderFile::FIELD_DIR_ID . ' = ' . (int) $dirId);

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
                'bind'       => ['search' => '%' . $filters[FinderConfig::FILTER_SEARCH] . '%']
            ]);

            return $this->getFiles($resultSet);
        }

        if (isset($filters[FinderConfig::FILTER_DIR])) {
            return $this->getByDir($filters[FinderConfig::FILTER_DIR]);
        }

        return $this->getByDir();
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
     * Create a map of thumbnails for the given finderFiles
     *
     * @param array $finderFiles
     * @return array [int fileId => string|null thumbnail]
     */
    public function getThumbNailMap(array $finderFiles)
    {
        $thumbNails = [];

        /** @var FinderFile $finderFile */
        foreach ($finderFiles as $finderFile) {
            $fileId = $finderFile->getId();

            if ( ! $this->isImage($finderFile)) {
                $thumbNails[$fileId] = null;
                continue;
            }

            $filePath  = $this->getFilePath($finderFile);
            $thumbPath = $this->getThumbPath($finderFile);

            if ( ! file_exists($thumbPath)) {
                $image = $this->imageHandler->create($filePath);
                $image->resize(96, 96);
                $image->save($thumbPath, 90);
            }

            $thumbNails[$fileId] = '/finder/thumb/' . $fileId;
        }

        return $thumbNails;
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
     * @param string $thumbDir
     */
    public function setThumbDir(string $thumbDir)
    {
        $this->thumbDir = $thumbDir;
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
     * @return bool
     */
    private function isImage(FinderFile $finderFile)
    {
        return in_array($finderFile->getMimeType(), self::IMAGE_TYPES);
    }
}