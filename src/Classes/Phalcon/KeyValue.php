<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use Exception;
use KikCMS\Config\CacheConfig;
use Monolog\Logger;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Cache;
use Psr\Log\LogLevel;

/**
 * This KeyValue is used to store a cache value on disk.
 * If the memory cache is available, it will try to utilise that first for better performance
 * @property Cache $cache
 */
class KeyValue extends Cache
{
    /** @var Cache|null */
    private ?Cache $memoryCache = null;

    /** @var Logger|null */
    private ?Logger $logger = null;

    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return parent::getAdapter();
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        $this->memoryCache?->delete($this->prefixKey($key));

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

        try {
            return parent::get($key, $defaultValue);
        } catch (Exception $exception){
            $this->logger?->log(LogLevel::WARNING, $exception, ['key' => $key]);

            return $defaultValue;
        }
    }

    /**
     * @inheritDoc
     */
    public function set($key = null, $value = null, $ttl = null): bool
    {
        $memoryTtl = $ttl ?: CacheConfig::ONE_DAY;

        $this->memoryCache?->set($this->prefixKey($key), $value, $memoryTtl);

        if($ttl === null){
            $ttl = CacheConfig::FOREVER;
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

    /**
     * @return Logger|null
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger|null $logger
     * @return KeyValue
     */
    public function setLogger(?Logger $logger): KeyValue
    {
        $this->logger = $logger;
        return $this;
    }
}