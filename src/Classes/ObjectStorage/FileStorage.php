<?php

namespace KikCMS\Classes\ObjectStorage;


use Phalcon\Http\Request\File as RequestFile;

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
     * @param RequestFile $file
     * @param string $dir
     * @param null $fileName
     * @return mixed
     */
    public function storeByRequest(RequestFile $file, string $dir = '', $fileName = null): bool;
}