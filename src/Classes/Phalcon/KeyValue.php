<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use KikCMS\Classes\Phalcon\Storage\Adapter\Stream;
use Phalcon\Cache;

/**
 * This KeyValue is used to store a cache value on disk.
 * If the memory cache is available, it will try to utilise that first for better performance
 * @property Cache $cache
 */
class KeyValue extends Cache
{
    /** @var Cache|null */
    private ?Cache $memoryCache;

    /**
     * @return Stream
     */
    public function getAdapter(): Stream
    {
        return parent::getAdapter();
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        if($this->memoryCache) {
            $this->memoryCache->delete($this->prefixKey($key));
        }

        return parent::delete($key);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $defaultValue = null)
    {
        if($this->memoryCache && $this->memoryCache->has($this->prefixKey($key))){
            return $this->memoryCache->get($this->prefixKey($key));
        }

        return parent::get($key, $defaultValue);
    }

    /**
     * @inheritDoc
     */
    public function set($key = null, $value = null, $ttl = null): bool
    {
        if($this->memoryCache) {
            $this->memoryCache->set($this->prefixKey($key), $value, $ttl);
        }

        return parent::set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function has($key = null): bool
    {
        if($this->memoryCache && $this->memoryCache->has($this->prefixKey($key))){
            return true;
        }

        return parent::has($key);
    }

    /**
     * @param string $key
     * @return string
     */
    private function prefixKey(string $key): string
    {
        return 'keyValueCache.' . $key;
    }

    /**
     * @return Cache|null
     */
    public function getMemoryCache(): ?Cache
    {
        return $this->memoryCache;
    }

    /**
     * @param Cache|null $memoryCache
     */
    public function setMemoryCache(?Cache $memoryCache): void
    {
        $this->memoryCache = $memoryCache;
    }
}