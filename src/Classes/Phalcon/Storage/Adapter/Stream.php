<?php


namespace KikCMS\Classes\Phalcon\Storage\Adapter;


use Phalcon\Support\HelperFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Stream extends \Phalcon\Cache\Adapter\Stream
{
    /**
     * @return string
     */
    public function getStorageDir(): string
    {
        return $this->options['storageDir'];
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->getStorageDir() . $this->getPrefix() . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritDoc
     */
    public function getKeys(string $prefix = ''): array
    {
        // add to prefix to get the right directory from Str::dirFromFile
        $prefixDir = $prefix . (strlen($prefix) % 2 ? ' ' : '  ');

        $helper = new HelperFactory();

        $dir = $this->getStoragePath() . $helper->dirFromFile($prefixDir);

        if ( ! file_exists($dir)) {
            return [];
        }

        $iterator = new RecursiveDirectoryIterator($dir);
        $keys     = [];

        foreach (new RecursiveIteratorIterator($iterator) as $file => $cur) {
            if ($cur->isDir()) {
                continue;
            }

            $keys[] = basename($file);
        }

        return $keys;
    }
}