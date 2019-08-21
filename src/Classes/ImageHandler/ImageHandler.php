<?php declare(strict_types=1);

namespace KikCMS\Classes\ImageHandler;


use Imagick;
use Phalcon\Image\Adapter;
use Phalcon\Image\Adapter\Imagick as PhalconImagick;

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
        $image = new PhalconImagick($filePath);

        $this->rotateImageByExifData($image);
        $this->stripExifData($image);

        return $image;
    }

    /**
     * @param PhalconImagick $image
     */
    private function rotateImageByExifData(PhalconImagick $image)
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
    private function stripExifData(PhalconImagick $image)
    {
        $imagickImage = $this->getImagick($image);
        $profiles     = $imagickImage->getImageProfiles("icc", true);

        $imagickImage->stripImage();

        if ( ! empty($profiles)) {
            $imagickImage->profileImage("icc", $profiles['icc']);
        }
    }

    /**
     * Get Imagick object from Phalcon Imagick Adapter
     *
     * @param PhalconImagick $image
     * @return Imagick
     */
    private function getImagick(PhalconImagick $image): Imagick
    {
        return $image->getImage();
    }
}