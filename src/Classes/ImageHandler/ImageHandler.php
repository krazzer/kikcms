<?php declare(strict_types=1);

namespace KikCMS\Classes\ImageHandler;


use Imagick;
use Phalcon\Image\Adapter\Imagick as PhalconImagick;

/**
 * Decouples Phalcon image Adapters
 */
class ImageHandler
{
    /**
     * @param string $filePath
     * @return PhalconImagick
     */
    public function create(string $filePath): PhalconImagick
    {
        $image = new PhalconImagick($filePath);

        $this->rotateImageByExifData($image);
        $this->stripExifData($image);

        return $image;
    }

    /**
     * @param PhalconImagick $image
     */
    private function rotateImageByExifData(PhalconImagick $image): void
    {
        $orientation = $this->getImagick($image)->getImageOrientation();

        switch ($orientation) {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotate(180);
            break;

            case Imagick::ORIENTATION_RIGHTTOP:
                $image->rotate(90);
            break;

            case Imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotate(-90);
            break;
        }
    }

    /**
     * Strips the image of it's Exif data, but keeping color profiles
     *
     * @param PhalconImagick $image
     */
    private function stripExifData(PhalconImagick $image): void
    {
        $imagickImage = $this->getImagick($image);
        $profiles     = $imagickImage->getImageProfiles("icc");

        $imagickImage->stripImage();

        if ( ! empty($profiles)) {
            $imagickImage->profileImage("icc", $profiles['icc']);
        }
    }

    /**
     * Get Imagick object from Phalcon Imagick Adapter
     *
     * @param PhalconImagick $image
     * @return object
     */
    private function getImagick(PhalconImagick $image): object
    {
        return $image->getImage();
    }
}