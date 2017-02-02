<?php

namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Database\Now;
use KikCMS\Classes\Storage\FileStorage;
use KikCMS\Models\FinderFile;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;

/**
 * Handles FinderFiles
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
     * @param int $dirId
     * @return int
     */
    public function create(File $file, $dirId = 0): int
    {
        $finderFile = new FinderFile();

        $finderFile->title     = $file->getName();
        $finderFile->extension = $file->getExtension();
        $finderFile->mimetype  = $file->getRealType();
        $finderFile->size      = $file->getSize();
        $finderFile->created   = new Now();
        $finderFile->updated   = new Now();
        $finderFile->dir_id    = $dirId;

        $finderFile->save();

        $this->fileStorage->store($file, $this->mediaDir, $finderFile->id);

        return (int) $finderFile->id;
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
}