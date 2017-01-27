<?php

namespace KikCMS\Classes\DataTable;


use Phalcon\Mvc\Model\Query\Builder;

/**
 * Extends the query builder for a dataTable by the given filters
 */
class FilterQueryBuilder
{
    const FILTER_SEARCH         = 'search';
    const FILTER_PAGE           = 'page';
    const FILTER_SORT_COLUMN    = 'sortColumn';
    const FILTER_SORT_DIRECTION = 'sortDirection';
    const FILTER_PARENT_EDIT_ID = 'parentEditId';

    /** @var DataTable */
    private $dataTable;

    /**
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * @param Builder $query
     * @param array $filters
     *
     * @return Builder
     */
    public function getQuery(Builder $query, array $filters): Builder
    {
        $this->addSearchFilter($query, $filters);
        $this->addSortFilter($query, $filters);
        $this->addSubDataTableFilter($query, $filters);

        return $query;
    }

    /**
     * @param Builder $query
     * @param array $filters
     */
    private function addSearchFilter(Builder $query, array $filters)
    {
        if ( ! isset($filters[self::FILTER_SEARCH])) {
            return;
        }

        $searchValue      = $filters[self::FILTER_SEARCH];
        $searchConditions = [];

        foreach ($this->dataTable->getSearchableFields() as $field) {
            $searchConditions[] = $field . ' LIKE "%' . $searchValue . '%"';
        }

        $query->andWhere(implode(' OR ', $searchConditions));
    }

    /**
     * @param Builder $query
     * @param array $filters
     */
    private function addSortFilter(Builder $query, array $filters)
    {
        if ( ! isset($filters[self::FILTER_SORT_COLUMN])) {
            return;
        }

        $column    = $filters[self::FILTER_SORT_COLUMN];
        $direction = $filters[self::FILTER_SORT_DIRECTION];

        if (in_array($direction, ['asc', 'desc'])) {
            if (array_key_exists($column, $this->dataTable->getOrderableFields())) {
                $column = $this->dataTable->getOrderableFields()[$column];
            }

            $query->orderBy('' . $column . ' ' . $direction);
        }
    }

    /**
     * @param Builder $query
     * @param array $filters
     */
    private function addSubDataTableFilter(Builder $query, array $filters)
    {
        if ( ! $this->dataTable->hasParent()) {
            return;
        }

        $parentEditId = $filters[self::FILTER_PARENT_EDIT_ID];
        $query->andWhere($this->dataTable->getParentRelationKey() . ' = ' . (int) $parentEditId);

        if ($parentEditId === 0) {
            $ids = $this->dataTable->getCachedNewIds();
            $query->inWhere($this->dataTable->getAliasedTableKey(), $ids);
        }
    }
}