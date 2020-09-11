<?php declare(strict_types=1);

namespace KikCMS\ObjectLists;

use KikCMS\Models\QueryLog;
use KikCmsCore\Classes\ObjectMap;

class QueryLogMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
