<?php declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Classes\Cache\CacheNode;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\CacheNodeMap;
use KikCmsCore\Services\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Cache\Cache;

/**
 * @property DbService $dbService
 * @property Cache $cache
 */
class CacheService extends Injectable
{
    /**
     * @param string $prefix
     */
    public function clear(string $prefix = ''): void
    {
        if ( ! $this->cache) {
            return;
        }

        if( ! $prefix){
            $this->cache->clear();
            return;
        }

        $keys = $this->getKeys($prefix);

        foreach ($keys as $cacheKey) {
            $this->cache->delete($cacheKey);
        }
    }

    /**
     * @param Page $page
     */
    public function clearForPage(Page $page): void
    {
        $offspring = $this->pageService->getOffspring($page);

        foreach ($offspring as $item){
            $this->cacheService->clear(CacheConfig::getUrlKeyForId($item->getId()));
        }

        $this->cacheService->clear(CacheConfig::getUrlKeyForId($page->getId()));
        $this->cacheService->clear(CacheConfig::MENU);
        $this->cacheService->clear(CacheConfig::PAGE_LANGUAGE_FOR_URL);
        $this->cacheService->clear(CacheConfig::PAGE_FOR_KEY);
    }

    /**
     * Clears all caches related to pages
     */
    public function clearPageCache(): void
    {
        $this->clear(CacheConfig::URL);
        $this->clear(CacheConfig::MENU);
        $this->clear(CacheConfig::MENU_PAGES);
        $this->clear(CacheConfig::PAGE_LANGUAGE_FOR_URL);
        $this->clear(CacheConfig::PAGE_LANGUAGE_FOR_KEY);
        $this->clear(CacheConfig::PAGE_FOR_KEY);
        $this->clear(CacheConfig::URL_FOR_KEY);

        $this->existingPageCacheService->clear();
    }

    /**
     * Clears all cached menu's
     */
    public function clearMenuCache(): void
    {
        $this->clear(CacheConfig::MENU);
        $this->clear(CacheConfig::MENU_PAGES);
    }

    /**
     * @param string $cacheKey
     * @param callable $function
     * @param float|int $ttl
     * @param bool $cacheNull if true, a NULL value may be cached
     * @return mixed|null
     */
    public function cache(string $cacheKey, callable $function, float|int $ttl = CacheConfig::ONE_DAY, bool $cacheNull = false): mixed
    {
        if ( ! $this->cache) {
            return $function();
        }

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $result = $function();

        if ($result != null || $cacheNull) {
            $this->cache->set($cacheKey, $result, $ttl);
        }

        return $result;
    }

    /**
     * @param array $args
     * @return string
     */
    public function createKey(...$args): string
    {
        return implode(CacheConfig::SEPARATOR, $args);
    }

    /**
     * Get a CacheNodeMap, which recursively contains all categories with their values
     *
     * @param string $prefix
     * @return CacheNodeMap
     */
    public function getCacheNodeMap(string $prefix = ''): CacheNodeMap
    {
        $cacheCategoryMap = new CacheNodeMap();

        $allKeys = $this->getKeys($prefix);

        sort($allKeys);

        foreach ($allKeys as $key) {
            $keyParts = explode(CacheConfig::SEPARATOR, $key);

            $subMap       = $cacheCategoryMap;
            $fullKeyParts = [];

            foreach ($keyParts as $keyPart) {
                $fullKeyParts[] = $keyPart;

                if ( ! $cacheNode = $subMap->get($keyPart)) {
                    $cacheNode = new CacheNode();
                    $subMap->add($cacheNode, $keyPart);
                }

                $cacheNode->setKey($keyPart);
                $cacheNode->setFullKey(implode(CacheConfig::SEPARATOR, $fullKeyParts));

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
     * @param string $prefix
     * @return array
     */
    public function getKeys(string $prefix = ''): array
    {
        if( ! $this->cache || ! $this->cache->getAdapter()){
            return [];
        }

        $mainPrefix = $this->cache->getAdapter()->getPrefix();

        $keys = $this->cache->getAdapter()->getKeys(preg_quote($prefix, '/'));

        if ( ! $mainPrefix) {
            return $keys;
        }

        foreach ($keys as &$key) {
            if($prefix && ! strstr($key, $prefix . CacheConfig::SEPARATOR) && $key !== $mainPrefix . $prefix){
                unset($key);
            } else {
                $key = substr($key, strlen($mainPrefix));
            }
        }

        return $keys;
    }
}