<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectList;
use Website\Models\Work;

class WorkList extends ObjectList
{
    /**
     * @inheritdoc
     * @return Work|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return Work|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return Work|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return Work|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
