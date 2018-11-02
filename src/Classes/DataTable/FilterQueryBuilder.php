<?php

namespace KikCMS\Classes\DataTable;


use KikCmsCore\Config\DbConfig;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Extends the query builder for a dataTable by the given filters
 */
class FilterQueryBuilder
{
    /** @var DataTable */
    private $dataTable;

    /** @var DataTableFilters */
    private $filters;

    /**
     * @param DataTable $dataTable
     * @param DataTableFilters $filters
     */
    public function __construct(DataTable $dataTable, DataTableFilters $filters)
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
        $this->addCustomFilters($query);

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
            if ($this->dataTable->isSortable()) {
                $query->orderBy($this->dataTable->getSortableField() . ' ' . DbConfig::SQL_SORT_ASCENDING);
            }

            return;
        }

        $column    = $this->filters->getSortColumn();
        $direction = $this->filters->getSortDirection();

        if (in_array($direction, DbConfig::SQL_SORT_DIRECTIONS)) {
            $query->orderBy($column . ' ' . $direction);
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
        $query->andWhere($this->dataTable->getParentRelationKey() . ' = ' . $this->dataTable->getParentRelationValue());

        if ($parentEditId === 0) {
            $ids = $this->dataTable->getCachedNewIds();
            $query->inWhere($this->dataTable->getAliasedTableKey(), $ids);
        }
    }

    /**
     * @param Builder $query
     */
    private function addCustomFilters(Builder $query)
    {
        $filterValues = $this->filters->getCustomFilterValues();

        foreach ($this->dataTable->getCustomFilters() as $field => $filter)
        {
            $value = array_key_exists($field, $filterValues) ? $filterValues[$field] : $filter->getDefault();

            if($value === null || $value === ''){
                continue;
            }

            $filter->applyFilter($query, $value);
        }
    }
}