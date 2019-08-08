<?php

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectMap;
use Website\Models\SimpleObject;

class SimpleObjectMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return SimpleObject|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return SimpleObject|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return SimpleObject|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return SimpleObject|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
