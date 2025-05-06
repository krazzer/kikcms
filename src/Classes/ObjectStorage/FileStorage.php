<?php declare(strict_types=1);

namespace KikCMS\Classes\ObjectStorage;


use Phalcon\Http\Request\File as UploadedFile;

interface FileStorage
{
    /**
     * @param string $fileName
     * @param string $dir
     * @return string
     */
    public function get(string $fileName, string $dir = ''): string;

    /**
     * @return mixed
     */
    public function getStorageDir(): mixed;

    /**
     * @param string $fileName
     * @param string $dir
     * @return bool
     */
    public function exists(string $fileName, string $dir = ''): bool;

    /**
     * @param mixed $storageDir
     * @return $this
     */
    public function setStorageDir(mixed $storageDir): static;

    /**
     * @param string $fileName
     * @param string $contents
     * @param string $dir
     */
    public function store(string $fileName, string $contents, string $dir = '');

    /**
     * @param UploadedFile $uploadedFile
     * @param string $dir
     * @param null $fileName
     * @param bool $overwrite
     * @return mixed
     */
    public function storeByRequest(UploadedFile $uploadedFile, string $dir = '', $fileName = null, bool $overwrite = false): bool;

    /**
     * @param string $fileContent
     * @param string $dir
     * @param string $fileName
     * @param string $extension
     * @param bool $overwrite
     * @return mixed
     */
    public function storeByData(string $fileContent, string $dir, string $fileName, string $extension, bool $overwrite = false): bool;
}