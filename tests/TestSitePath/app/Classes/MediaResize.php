<?php

namespace Website\Classes;


use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use Phalcon\Image\Adapter;

class MediaResize extends MediaResizeBase
{
    /**
     * Will automatically resize an image called by twig shortcut: mediaFile(fileId)
     *
     * @param Adapter $image
     */
    public function resizeExample(Adapter $image)
    {
        $this->resize($image, 50, 50);
    }
}