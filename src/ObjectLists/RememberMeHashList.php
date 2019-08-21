<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Objects\RememberMeHash;
use KikCmsCore\Classes\ObjectList;

class RememberMeHashList extends ObjectList
{
    /**
     * @param int|string $key
     * @return RememberMeHash|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return RememberMeHash|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return RememberMeHash|false
     */
    public function getLast()
    {
        return parent::getLast();
    }

    /**
     * @return RememberMeHash|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @return string|null
     */
    public function serialize(): ?string
    {
        return $this->isEmpty() ? null : serialize($this);
    }
}