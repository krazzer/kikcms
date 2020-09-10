<?php declare(strict_types=1);


namespace KikCMS\Services\Util;


use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service to get info from a query, not executing them
 */
class QueryService extends Injectable
{
    /**
     * @param Builder $query
     * @return array
     */
    public function getAliases(Builder $query): array
    {
        $aliases = [];

        if ($alias = $this->getFromAlias($query)) {
            $aliases[] = $alias;
        }

        if ( ! $joins = $query->getJoins()) {
            return $aliases;
        }

        foreach ($joins as $join) {
            $aliases[] = $join[2];
        }

        return $aliases;
    }

    /**
     * @param Builder $query
     * @return string|null
     */
    public function getFromAlias(Builder $query): ?string
    {
        $from = $query->getFrom();

        return is_array($from) ? key($from) : null;
    }
}