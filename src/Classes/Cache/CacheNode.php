<?php


namespace KikCMS\Classes\Cache;


use KikCMS\ObjectLists\CacheNodeMap;

class CacheNode
{
    /** @var string */
    private $key;

    /** @var string */
    private $fullKey;

    /** @var mixed */
    private $value;

    /** @var CacheNodeMap */
    private $cacheNodeMap;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return CacheNode
     */
    public function setKey(string $key): CacheNode
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return CacheNodeMap
     */
    public function getCacheNodeMap(): CacheNodeMap
    {
        if ( ! $this->cacheNodeMap) {
            $this->cacheNodeMap = new CacheNodeMap();
        }

        return $this->cacheNodeMap;
    }

    /**
     * @param CacheNodeMap $cacheNodeMap
     * @return CacheNode
     */
    public function setCacheNodeMap(CacheNodeMap $cacheNodeMap): CacheNode
    {
        $this->cacheNodeMap = $cacheNodeMap;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueOutput(): string
    {
        if($this->getCacheNodeMap()->count() > 0){
            return '';
        }

        if ($this->value === null){
            return 'null';
        }

        if (is_bool($this->value)) {
            return '(bool) ' . ($this->value ? 'true' : 'false');
        }

        if (is_scalar($this->value)) {
            return $this->value;
        }

        return serialize($this->value);
    }

    /**
     * @param mixed $value
     * @return CacheNode
     */
    public function setValue($value): CacheNode
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullKey(): string
    {
        return $this->fullKey;
    }

    /**
     * @param string $fullKey
     * @return CacheNode
     */
    public function setFullKey(string $fullKey): CacheNode
    {
        $this->fullKey = $fullKey;
        return $this;
    }

    /**
     * Recursively get the amount of nodes
     *
     * @return int
     */
    public function getTotal(): int
    {
        $total = 0;

        if ( ! $this->getCacheNodeMap()->count()) {
            return 1;
        }

        foreach ($this->getCacheNodeMap() as $node) {
            $total += $node->getTotal();
        }

        return $total;
    }

    /**
     * Find nodes that have only one child, and merge it with the parent
     */
    public function flattenSingleNodes()
    {
        if ($this->getCacheNodeMap()->count() === 1) {
            $cacheNode = $this->getCacheNodeMap()->getFirst();
            $cacheNode->flattenSingleNodes();

            $this
                ->setCacheNodeMap($cacheNode->getCacheNodeMap())
                ->setFullKey($cacheNode->getFullKey())
                ->setValue($cacheNode->getValue())
                ->setKey($this->getKey() . ':' . $cacheNode->getKey());
        }

        if ($this->getCacheNodeMap()->count() > 1) {
            foreach ($this->getCacheNodeMap() as $cacheNode) {
                $cacheNode->flattenSingleNodes();
            }
        }
    }
}