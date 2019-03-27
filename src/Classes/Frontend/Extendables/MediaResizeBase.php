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
        $sourceWidth = $image->getWidth();
        $sourceHeight = $image->getHeight();

        if ($sourceWidth < $width && $sourceHeight < $height) {
            return;
        }

        $sourceAspectRatio  = $sourceWidth / $sourceHeight;
        $desiredAspectRatio = $width / $height;

        if ($sourceAspectRatio > $desiredAspectRatio) {
            $newHeight = $height;
            $newWidth  = (int) ($height * $sourceAspectRatio);
        } else {
            $newWidth  = $width;
            $newHeight = (int) ($width / ($sourceAspectRatio));
        }

        $x0 = ($newWidth - $width) / 2;
        $y0 = ($newHeight - $height) / 2;

        if($newWidth != $sourceWidth || $newHeight != $sourceHeight){
            $image->resize($newWidth, $newHeight);
        }

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

        $ratio = $image->getWidth() / $image->getHeight();

        if ($ratio < 1) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $image->resize($width, $height);
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