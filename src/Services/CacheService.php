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
        if( ! $this->cache){
            return;
        }

        $keys = $this->cache->queryKeys($prefix);

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
        if( ! $this->cache){
            return $function();
        }

        if ($this->cache->exists($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $result = $function();

        if($result !== null){
            $this->cache->save($cacheKey, $result, $ttl);
        }

        return $result;
    }
}