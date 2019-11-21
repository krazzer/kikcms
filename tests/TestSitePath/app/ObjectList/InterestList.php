<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectList;
use Website\Models\Interest;

class InterestList extends ObjectList
{
    /**
     * @inheritdoc
     * @return Interest|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return Interest|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return Interest|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return Interest|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
