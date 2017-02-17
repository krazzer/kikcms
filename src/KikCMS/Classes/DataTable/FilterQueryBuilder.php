<?php

namespace KikCMS\Classes\DataTable;


use Phalcon\Mvc\Model\Query\Builder;

/**
 * Extends the query builder for a dataTable by the given filters
 */
class FilterQueryBuilder
{
    /** @var DataTable */
    private $dataTable;

    /** @var Filters */
    private $filters;

    /**
     * @param DataTable $dataTable
     * @param Filters $filters
     */
    public function __construct(DataTable $dataTable, Filters $filters)
    {
        $this->dataTable = $dataTable;
        $this->filters   = $filters;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function getQuery(Builder $query): Builder
    {
        $this->addSearchFilter($query);
        $this->addSortFilter($query);
        $this->addSubDataTableFilter($query);

        return $query;
    }

    /**
     * @param Builder $query
     */
    private function addSearchFilter(Builder $query)
    {
        if ( ! $searchValue = $this->filters->getSearch()) {
            return;
        }

        $searchConditions = [];

        foreach ($this->dataTable->getSearchableFields() as $field) {
            $searchConditions[] = $field . ' LIKE "%' . $searchValue . '%"';
        }

        $query->andWhere(implode(' OR ', $searchConditions));
    }

    /**
     * @param Builder $query
     */
    private function addSortFilter(Builder $query)
    {
        if ( ! $this->filters->getSortColumn()) {
            return;
        }

        $column    = $this->filters->getSortColumn();
        $direction = $this->filters->getSortDirection();

        if (in_array($direction, ['asc', 'desc'])) {
            if (array_key_exists($column, $this->dataTable->getOrderableFields())) {
                $column = $this->dataTable->getOrderableFields()[$column];
            }

            $query->orderBy('' . $column . ' ' . $direction);
        }
    }

    /**
     * @param Builder $query
     */
    private function addSubDataTableFilter(Builder $query)
    {
        if ( ! $this->dataTable->hasParent()) {
            return;
        }

        $parentEditId = $this->filters->getParentEditId();
        $query->andWhere($this->dataTable->getParentRelationKey() . ' = ' . (int) $parentEditId);

        if ($parentEditId === 0) {
            $ids = $this->dataTable->getCachedNewIds();
            $query->inWhere($this->dataTable->getAliasedTableKey(), $ids);
        }
    }
}