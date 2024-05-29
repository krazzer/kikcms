<?php
declare(strict_types=1);

namespace KikCMS\Services\Finder;


use Exception;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\File;
use KikCMS\Classes\Phalcon\Injectable;
use Monolog\Logger;

/**
 * @property ImageHandler $imageHandler
 * @property FileService $fileService
 * @property IniConfig $config
 */
class FileResizeService extends Injectable
{
    public function resizeWithinBoundaries(File $file): void
    {
        // is no image, so do nothing
        if ( ! $file->isImage()) {
            return;
        }

        $filePath = $this->fileService->getFilePath($file);

        $jpgQuality = (int) $this->config->media->jpgQuality;
        $maxWidth   = (int) $this->config->media->maxWidth;
        $maxHeight  = (int) $this->config->media->maxHeight;

        $image = $this->imageHandler->create($filePath);

        // if dimensions are larger than the maximum, resize
        if ($image->getWidth() > $maxWidth || $image->getHeight() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight);
        }

        try {
            $image->save($filePath, $jpgQuality);
        } catch (Exception $exception){
            // just ignore the resize if this error occurs. Likely to happen with animated gifs
            if($exception->getCode() != FinderConfig::ERROR_CODE_IMAGES_NOT_SAME_SIZE) {
                $this->logger->log(Logger::ERROR, $exception->getMessage(), $exception->getTrace());
            }
        }
    }
}