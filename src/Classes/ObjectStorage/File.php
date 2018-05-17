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
    public function exists(string $fileName, string $dir = ''): bool
    {
        return file_exists($this->getStorageDir() . $dir . '/' . $fileName);
    }

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
    public function storeByRequest(RequestFile $file, string $dir = '', $fileName = null): bool
    {
        if ( ! $fileName) {
            $fileName = $file->getName();
        }

        $filePath = $this->getStorageDir() . $dir . '/' . $fileName . '.' . $file->getExtension();

        return $file->moveTo($filePath);
    }

    /**
     * @inheritdoc
     */
    public function store(string $fileName, string $contents, string $dir = '')
    {
        $filePath = $this->getStorageDir() . $dir . '/' . $fileName;
        return file_put_contents($filePath, $contents);
    }
}