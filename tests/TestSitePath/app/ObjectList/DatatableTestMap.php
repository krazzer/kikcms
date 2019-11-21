<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectMap;
use Website\Models\DatatableTest;

class DatatableTestMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return DatatableTest|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return DatatableTest|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return DatatableTest|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return DatatableTest|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
