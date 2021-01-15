<?php


namespace KikCMS\Classes\Phalcon\Storage\Adapter;


class Stream extends \Phalcon\Cache\Adapter\Stream
{
    /**
     * @return string
     */
    public function getStorageDir(): string
    {
        return $this->options['storageDir'];
    }
}