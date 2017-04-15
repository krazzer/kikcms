<?php

namespace KikCMS\Classes\ImageHandler;


use Phalcon\Image\Adapter;
use Phalcon\Image\Adapter\Imagick;

/**
 * Decouples Phalcon image Adapters
 */
class ImageHandler
{
    /**
     * @param string $filePath
     * @return Adapter
     */
    public function create(string $filePath)
    {
        return new Imagick($filePath);
    }
}