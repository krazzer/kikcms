<?php declare(strict_types=1);

namespace KikCMS\Classes\ObjectStorage;


use Phalcon\Http\Request\File as UploadedFile;

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
    public function get(string $fileName, string $dir = ''): string
    {
        return (string) file_get_contents($this->getStorageDir() . $dir . '/' . $fileName);
    }

    /**
     * @inheritdoc
     */
    public function getStorageDir(): mixed
    {
        return $this->storageDir;
    }

    /**
     * @inheritdoc
     */
    public function setStorageDir(mixed $storageDir): static
    {
        $this->storageDir = $storageDir;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function storeByRequest(UploadedFile $uploadedFile, string $dir = '', $fileName = null, bool $overwrite = false): bool
    {
        if ( ! $fileName) {
            $fileName = $uploadedFile->getName();
        }

        $filePath = $this->getStorageDir() . $dir . '/' . $fileName . '.' . strtolower($uploadedFile->getExtension());

        if ($overwrite && file_exists($filePath)) {
            unlink($filePath);
        }

        return $uploadedFile->moveTo($filePath);
    }

    /**
     * @inheritdoc
     */
    public function store(string $fileName, string $contents, string $dir = ''): false|int
    {
        $filePath = $this->getStorageDir() . $dir . '/' . $fileName;
        return file_put_contents($filePath, $contents);
    }
}