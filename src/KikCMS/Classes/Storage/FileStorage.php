<?php

namespace KikCMS\Classes\Storage;


use Phalcon\Http\Request\File as RequestFile;

interface FileStorage
{
    /**
     * @param RequestFile $file
     * @param string $dir
     * @param string|null $fileName
     */
    public function store(RequestFile $file, string $dir = '', $fileName = null);
}