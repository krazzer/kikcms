<?php

namespace KikCMS\Classes\ObjectStorage;


use Phalcon\Http\Request\File as RequestFile;

/**
 * Stores files on disk
 */
class File implements FileStorage
{
    private $storageDir;

    /**
     * @inheritdoc
     */
    public function getStorageDir()
    {
        return $this->storageDir;
    }

    /**
     * @inheritdoc
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

        $filePath = $this->getStorageDir() . $dir . '/' . $fileName . '.' . $file->getExtension();

        $file->moveTo($filePath);
    }
}