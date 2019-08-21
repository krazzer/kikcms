<?php declare(strict_types=1);


namespace KikCMS\Services\Finder;


use KikCMS\Config\CacheConfig;
use KikCMS\Config\PlaceholderConfig;
use KikCMS\Models\File;
use KikCMS\Services\CacheService;
use Phalcon\Di\Injectable;

/**
 * @property CacheService $cacheService
 */
class FileCacheService extends Injectable
{
    /**
     * @param File $file
     */
    public function removeUrlCache(File $file)
    {
        $this->cacheService->clear(PlaceholderConfig::FILE_URL . CacheConfig::SEPARATOR . $file->getId());
        $this->cacheService->clear(PlaceholderConfig::FILE_THUMB_URL . CacheConfig::SEPARATOR . $file->getId());
    }
}