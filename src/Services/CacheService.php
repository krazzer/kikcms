<?php

namespace KikCMS\Services;


use KikCMS\Classes\Cache\CacheNode;
use KikCMS\ObjectLists\CacheNodeMap;
use KikCmsCore\Services\DbService;
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
     * @param string|null $prefix
     */
    public function clear(string $prefix = null)
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
     * Clears all cached menu's
     */
    public function clearMenuCache()
    {
        $this->clear(CacheConfig::MENU);
        $this->clear(CacheConfig::MENU_PAGES);
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
     * @param array $args
     * @return string
     */
    public function createKey(...$args): string
    {
        return implode(':', $args);
    }

    /**
     * Get a CacheNodeMap, which recursively contains all categories with their values
     *
     * @param string|null $prefix
     * @return CacheNodeMap
     */
    public function getCacheNodeMap(string $prefix = null): CacheNodeMap
    {
        $cacheCategoryMap = new CacheNodeMap();

        $allKeys = $this->getKeys($prefix);

        sort($allKeys);

        foreach ($allKeys as $key) {
            $keyParts = explode(':', $key);

            $subMap       = $cacheCategoryMap;
            $fullKeyParts = [];

            foreach ($keyParts as $keyPart) {
                $fullKeyParts[] = $keyPart;

                if ( ! $cacheNode = $subMap->get($keyPart)) {
                    $cacheNode = new CacheNode();
                    $subMap->add($cacheNode, $keyPart);
                }

                $cacheNode->setKey($keyPart);
                $cacheNode->setFullKey(implode(':', $fullKeyParts));

                if ($keyPart == last($keyParts)) {
                    $cacheNode->setValue($this->cache->get($key));
                } else {
                    $subMap = $cacheNode->getCacheNodeMap();
                }
            }
        }

        foreach ($cacheCategoryMap as $cacheNode){
            $cacheNode->flattenSingleNodes();
        }

        return $cacheCategoryMap;
    }

    /**
     * @param string|null $prefix
     * @return array
     */
    public function getKeys(string $prefix = null): array
    {
        $mainPrefix = $this->getMainPrefix();

        $keys = $this->cache->queryKeys($mainPrefix . preg_quote($prefix, '/'));

        if ( ! $mainPrefix) {
            return $keys;
        }

        foreach ($keys as &$key) {
            if($prefix && ! strstr($key, $prefix . ':') && $key !== $mainPrefix . $prefix){
                unset($key);
            } else {
                $key = substr($key, strlen($mainPrefix));
            }
        }

        return $keys;
    }

    /**
     * Get the caches' main prefix
     *
     * @return string|null
     */
    private function getMainPrefix(): ?string
    {
        if ( ! $this->cache->getOptions()) {
            return null;
        }

        if ( ! array_key_exists('prefix', $this->cache->getOptions())) {
            return null;
        }

        return $this->cache->getOptions()['prefix'];
    }
}