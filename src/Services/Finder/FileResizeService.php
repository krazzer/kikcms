<?php
declare(strict_types=1);

namespace KikCMS\Services\Finder;


use ImagickException;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Models\File;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * @property ImageHandler $imageHandler
 * @property FileService $fileService
 * @property IniConfig $config
 */
class FileResizeService extends Injectable
{
    public function resizeWithinBoundaries(File $file)
    {
        // is no image, so do nothing
        if ( ! $file->isImage()) {
            return;
        }

        $filePath = $this->fileService->getFilePath($file);

        $jpgQuality = $this->config->media->jpgQuality;
        $maxWidth   = $this->config->media->maxWidth;
        $maxHeight  = $this->config->media->maxHeight;

        $image = $this->imageHandler->create($filePath);

        // if dimensions are larger then the maximum, resize
        if ($image->getWidth() > $maxWidth || $image->getHeight() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight);
        }

        try {
            $image->save($filePath, $jpgQuality);
        } catch(ImagickException $exception){
            // ignore error gif resize with different sizes
            if( ! $exception->getCode() === 410){
                throw $exception;
            }
        }
    }
}