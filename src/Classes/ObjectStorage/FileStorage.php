<?php declare(strict_types=1);

namespace KikCMS\Classes\ObjectStorage;


use Phalcon\Http\Request\File as UploadedFile;

interface FileStorage
{
    /**
     * @return mixed
     */
    public function getStorageDir();

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
    public function setStorageDir($storageDir);

    /**
     * @param string $fileName
     * @param string $dir
     * @param string $contents
     * @return
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
}