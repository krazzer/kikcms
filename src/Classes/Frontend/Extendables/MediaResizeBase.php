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
        if($image->getWidth() < $width && $image->getHeight() < $height){
            return;
        }

        if($image->getWidth() / $image->getHeight() > $width / $height){
            $image->resize($width * ($image->getHeight() / $height), $height);
        } else {
            $image->resize($width, $height * ($image->getWidth() / $width));
        }

        // crop if the width or height is still out of bounds
        if($image->getWidth() > $width || $image->getHeight() > $height){
            $offsetX = ($image->getWidth() - $width) / 2;
            $offsetY = ($image->getHeight() - $height) / 2;

            $image->crop($width, $height, $offsetX, $offsetY);
        }
    }

    /**
     * @param Adapter $image
     * @param $width
     * @param $height
     */
    public function resize(Adapter $image, $width, $height)
    {
        if($image->getWidth() < $width && $image->getHeight() < $height){
            return;
        }

        $image->resize($width, $height);

        // resize again if the width or height is still out of bounds
        if($image->getWidth() > $width || $image->getHeight() > $height){
            $image->resize($width, $height);
        }
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