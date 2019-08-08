<?php

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectList;
use Website\Models\DataTableTestChild;

class DatatableTestChildList extends ObjectList
{
    /**
     * @inheritdoc
     * @return DataTableTestChild|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return DataTableTestChild|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return DataTableTestChild|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return DataTableTestChild|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
