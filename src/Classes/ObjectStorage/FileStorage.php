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
     * @param mixed $storageDir
     * @return $this
     */
    public function setStorageDir($storageDir);

    /**
     * @param RequestFile $file
     * @param string $dir
     * @param string|null $fileName
     */
    public function store(RequestFile $file, string $dir = '', $fileName = null);
}