<?php

namespace Website\TestClasses;


use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use Phalcon\Image\Adapter\AbstractAdapter;

class MediaResize extends MediaResizeBase
{
    /**
     * Will automatically resize an image called by twig shortcut: mediaFile(fileId)
     *
     * @param AbstractAdapter $image
     */
    public function resizeExample(AbstractAdapter $image)
    {
        $this->resize($image, 50, 50);
    }
}