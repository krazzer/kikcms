<?php

namespace KikCMS\Services\PageCache;

use KikCMS\Classes\Phalcon\Injectable;

class PageCacheService extends Injectable
{
    /**
     * Save the HTML of a page in the cache
     *
     * @param string $path
     * @param string $content
     * @return bool
     */
    public function save(string $path, string $content): bool
    {
        $pageCachePath = $this->getCachePathByUrlPath($path);

        if ( ! file_exists(dirname($pageCachePath))) {
            mkdir(dirname($pageCachePath), 0775, true);
        }

        return (bool) file_put_contents($pageCachePath, $content . "\n<!-- CACHED BY PAGECACHE -->");
    }

    /**
     * @param string $path
     * @return string
     */
    public function getCachePathByUrlPath(string $path): string
    {
        return $this->getCachePath() . ($path ?: 'index.real') . '.html';
    }

    /**
     * @return string
     */
    public function getCachePath(): string
    {
        return $this->config->application->path . $this->config->application->publicFolder . '/pagecache/';
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getContentByUrlPath(string $path): ?string
    {
        $filePath = $this->getCachePathByUrlPath($path);

        if( ! file_exists($filePath)){
            return null;
        }

        return (string) file_get_contents($filePath);
    }
}