<?php

namespace KikCMS\Services;


use KikCMS\Classes\DbService;
use KikCMS\Config\CacheConfig;
use Phalcon\Cache\Backend;
use Phalcon\Di\Injectable;

/**
 * @property DbService $dbService
 * @property Backend $cache
 */
class CacheService extends Injectable
{
    /**
     * @param string $prefix
     */
    public function clear(string $prefix)
    {
        if ( ! $this->cache) {
            return;
        }


        $keys = $this->getKeys($prefix);

        foreach ($keys as $cacheKey) {
            $this->cache->delete($cacheKey);
        }
    }

    /**
     * Clears all caches related to pages
     */
    public function clearPageCache()
    {
        $this->clear(CacheConfig::URL);
        $this->clear(CacheConfig::MENU);
        $this->clear(CacheConfig::MENU_PAGES);
        $this->clear(CacheConfig::PAGE_LANGUAGE_FOR_URL);
    }

    /**
     * @param string $cacheKey
     * @param callable $function
     * @param int $ttl
     *
     * @return mixed|null
     */
    public function cache(string $cacheKey, callable $function, $ttl = CacheConfig::ONE_DAY)
    {
        if ( ! $this->cache) {
            return $function();
        }

        if ($this->cache->exists($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $result = $function();

        if ($result !== null) {
            $this->cache->save($cacheKey, $result, $ttl);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function createKey(): string
    {
        return implode(':', func_get_args());
    }

    /**
     * Get the caches' main prefix
     *
     * @return string|null
     */
    private function getMainPrefix(): ?string
    {
        if( ! $this->cache->getOptions()){
            return null;
        }

        if ( ! array_key_exists('prefix', $this->cache->getOptions())) {
            return null;
        }

        return $this->cache->getOptions()['prefix'];
    }

    /**
     * @param string $prefix
     * @return array
     */
    private function getKeys(string $prefix): array
    {
        $mainPrefix = $this->getMainPrefix();

        $keys = $this->cache->queryKeys($mainPrefix . $prefix);

        if( ! $mainPrefix){
            return $keys;
        }

        foreach ($keys as &$key){
            $key = substr($key, strlen($mainPrefix));
        }

        return $keys;
    }
}