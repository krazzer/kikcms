<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;


use KikCMS\Models\User;
use KikCmsCore\Classes\ObjectMap;

class UserMap extends ObjectMap
{
    /**
     * @param int|string $key
     * @return User|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @return User|false
     */
    public function current()
    {
        return parent::current();
    }
}