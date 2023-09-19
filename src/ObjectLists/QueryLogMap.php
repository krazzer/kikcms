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
    public function current(): QueryLog|false
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function get($key): QueryLog|false
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function getFirst(): QueryLog|false
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return QueryLog|false
     */
    public function getLast(): QueryLog|false
    {
        return parent::getLast();
    }
}
