<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\CacheConfig;

class ExistingPageCacheService extends Injectable
{
    /**
     * Build up cache for existing links
     */
    public function buildCache()
    {
        $links = $this->urlService->getUrls(true);

        $this->keyValue->set(CacheConfig::EXISTING_PAGE_CACHE, json_encode($links));
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return (array) json_decode($this->keyValue->get(CacheConfig::EXISTING_PAGE_CACHE));
    }

    /**
     * @param string|null $urlPath
     * @return bool|null (true = exists, false = does not exist, null = unknown)
     */
    public function exists(?string $urlPath): ?bool
    {
        if( ! $urls = $this->getUrls()){
            return null;
        }

        return in_array('/' . $urlPath, $urls);
    }

    /**
     * Clear cache
     */
    public function clear()
    {
        $this->keyValue->delete(CacheConfig::EXISTING_PAGE_CACHE);
        $this->keyValue->delete(CacheConfig::PAGE_404);
    }

    /**
     * @param string $content
     * @return void
     */
    public function cache404Page(string $content)
    {
        $this->keyValue->set(CacheConfig::PAGE_404, $content);
    }

    /**
     * @return string|null
     */
    public function get404PageContent(): ?string
    {
        return $this->keyValue->get(CacheConfig::PAGE_404);
    }

    /**
     * @return bool
     */
    public function cacheIsBuild(): bool
    {
        return $this->keyValue->has(CacheConfig::EXISTING_PAGE_CACHE);
    }
}