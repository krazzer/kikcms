<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use Phalcon\Cache\Backend\File;
use Phalcon\Cache\BackendInterface;

/**
 * This KeyValue is used to store a cache value on disk.
 * If the memory cache is available, it will try to utilise that first for better performance
 * @property BackendInterface $cache
 */
class KeyValue extends File
{
    /** @var null|BackendInterface */
    private $memoryCache;

    /**
     * @inheritDoc
     */
    public function delete($keyName): bool
    {
        if($this->memoryCache) {
            $this->memoryCache->delete($this->prefixKey($keyName));
        }

        return parent::delete($keyName);
    }

    /**
     * @inheritDoc
     */
    public function get($keyName, $lifetime = null)
    {
        $memoryCacheLifeTime = $lifetime ?: $this->getFrontend()->getLifeTime();

        if($this->memoryCache && $this->memoryCache->exists($this->prefixKey($keyName), $memoryCacheLifeTime)){
            return $this->memoryCache->get($this->prefixKey($keyName), $memoryCacheLifeTime);
        }

        return parent::get($keyName, $lifetime);
    }

    /**
     * @inheritDoc
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true): bool
    {
        $memoryCacheLifeTime = $lifetime ?: $this->getFrontend()->getLifeTime();

        if($this->memoryCache) {
            $this->memoryCache->save($this->prefixKey($keyName), $content, $memoryCacheLifeTime);
        }

        return parent::save($keyName, $content, $lifetime, $stopBuffer);
    }

    /**
     * @inheritDoc
     */
    public function exists($keyName = null, $lifetime = null): bool
    {
        $memoryCacheLifeTime = $lifetime ?: $this->getFrontend()->getLifeTime();

        if($this->memoryCache && $this->memoryCache->exists($this->prefixKey($keyName), $memoryCacheLifeTime)){
            return true;
        }

        return parent::exists($keyName, $lifetime);
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
     * @return BackendInterface|null
     */
    public function getMemoryCache(): ?BackendInterface
    {
        return $this->memoryCache;
    }

    /**
     * @param BackendInterface|null $memoryCache
     */
    public function setMemoryCache(?BackendInterface $memoryCache): void
    {
        $this->memoryCache = $memoryCache;
    }
}