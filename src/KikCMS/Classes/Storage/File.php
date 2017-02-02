<?php

namespace KikCMS\Classes\Storage;


use Phalcon\Http\Request\File as RequestFile;

/**
 * Stores files on disk
 */
class File implements FileStorage
{
    private $storageDir;

    /**
     * @return mixed
     */
    public function getStorageDir()
    {
        return $this->storageDir;
    }

    /**
     * @param mixed $storageDir
     * @return File
     */
    public function setStorageDir($storageDir)
    {
        $this->storageDir = $storageDir;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function store(RequestFile $file, string $dir = '', $fileName = null)
    {
        if ( ! $fileName) {
            $fileName = $file->getName();
        }

        $file->moveTo($this->getStorageDir() . $dir . '/' . $fileName);
    }
}