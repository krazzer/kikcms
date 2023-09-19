<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Classes\Frontend\FullPage;
use KikCmsCore\Classes\ObjectMap;

class FullPageMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return FullPage|false
     */
    public function get($key): FullPage|false
    {
        return parent::get($key);
    }
    /**
     * @return FullPage|false
     */
    public function getFirst(): FullPage|false
    {
        return parent::getFirst();
    }

    /**
     * @return FullPage|false
     */
    public function current(): FullPage|false
    {
        return parent::current();
    }

    /**
     * @return ObjectMap|FullPageMap|false
     */
    public function reverse(): ObjectMap|FullPageMap|false
    {
        return parent::reverse();
    }

    /**
     * @inheritDoc
     * @return FullPage[]|Object[]
     */
    public function getObjects(): array
    {
        return parent::getObjects();
    }
}