<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Util\StringUtil;
use Phalcon\Image\Adapter;

/**
 * Contains methods to resize thumbnails in predefined formats
 */
class MediaResizeBase extends WebsiteExtendable
{
    /**
     * @param string|null $type
     * @return bool
     */
    public function typeExists(string $type = null): bool
    {
        if ( ! $type) {
            return true;
        }

        return $this->typeMethodExists($type);
    }

    /**
     * @param Adapter $image
     * @param $width
     * @param $height
     */
    public function crop(Adapter $image, $width, $height)
    {
        if ($image->getWidth() < $width && $image->getHeight() < $height) {
            return;
        }

        // resize first to maintain aspect ratio
        $this->resize($image, $width, $height);

        $x0 = ($image->getWidth() - $width) / 2;
        $y0 = ($image->getHeight() - $height) / 2;

        $image->crop($width, $height, $x0, $y0);
    }

    /**
     * @param Adapter $image
     * @param $width
     * @param $height
     */
    public function resize(Adapter $image, $width, $height)
    {
        if ($image->getWidth() < $width && $image->getHeight() < $height) {
            return;
        }

        $sourceHeight = $image->getHeight();
        $sourceWidth  = $image->getWidth();

        $sourceAspectRatio  = $sourceWidth / $sourceHeight;
        $desiredAspectRatio = $width / $height;

        if ($sourceAspectRatio > $desiredAspectRatio) {
            $newHeight = $height;
            $newWidth  = (int) ($height * $sourceAspectRatio);
        } else {
            $newWidth  = $width;
            $newHeight = (int) ($width / ($sourceAspectRatio));
        }

        $image->resize($newWidth, $newHeight);
    }

    /**
     * @param Adapter $image
     * @param string $type
     */
    public function resizeByType(Adapter $image, string $type)
    {
        if ( ! $this->typeMethodExists($type)) {
            $this->throwMethodDoesNotExistException($this->getMethod($type));
        }

        $method = $this->getMethod($type);
        $this->$method($image);
    }

    /**
     * @param Adapter $image
     */
    public function resizeDefault(Adapter $image)
    {
        $this->resize($image, 192, 192);
    }

    /**
     * @param $type
     * @return bool
     */
    private function typeMethodExists($type): bool
    {
        return method_exists($this, $this->getMethod($type));
    }

    /**
     * @param $type
     * @return string
     */
    private function getMethod($type): string
    {
        return 'resize' . StringUtil::dashesToCamelCase($type, true);
    }
}