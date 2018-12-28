<?php


namespace KikCMS\Services\DataTable;


use KikCMS\Classes\DataTable\DataTableFilters;
use KikCMS\Classes\DataTable\Filter\Filter;
use KikCMS\Services\ModelService;
use KikCmsCore\Config\DbConfig;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Relation;

/**
 * Handles DataTableFilters for DataTables
 *
 * @property ModelService $modelService
 */
class DataTableFilterService extends Injectable
{
    /**
     * @param Builder $query
     * @param DataTableFilters $filters
     * @param Filter[] $customFilters
     */
    public function addCustomFilters(Builder $query, DataTableFilters $filters, array $customFilters)
    {
        $filterValues = $filters->getCustomFilterValues();

        foreach ($customFilters as $field => $filter) {
            $value = array_key_exists($field, $filterValues) ? $filterValues[$field] : $filter->getDefault();

            if ($value === null || $value === '') {
                continue;
            }

            $filter->applyFilter($query, $value);
        }
    }

    /**
     * @param Builder $query
     * @param DataTableFilters $filters
     * @param array $searchFields
     */
    public function addSearchFilter(Builder $query, DataTableFilters $filters, array $searchFields)
    {
        if ( ! $searchValue = $filters->getSearch()) {
            return;
        }

        $searchConditions = [];

        foreach ($searchFields as $field) {
            $searchConditions[] = $field . ' LIKE "%' . $searchValue . '%"';
        }

        $query->andWhere(implode(' OR ', $searchConditions));
    }

    /**
     * @param Builder $query
     * @param DataTableFilters $filters
     * @param bool $isSortable
     * @param string $sortableField
     */
    public function addSortFilter(Builder $query, DataTableFilters $filters, bool $isSortable, string $sortableField)
    {
        if ( ! $filters->getSortColumn()) {
            if ($isSortable) {
                $query->orderBy($sortableField . ' ' . DbConfig::SQL_SORT_ASCENDING);
            }

            return;
        }

        $column    = $filters->getSortColumn();
        $direction = $filters->getSortDirection();

        if (in_array($direction, DbConfig::SQL_SORT_DIRECTIONS)) {
            $query->orderBy($column . ' ' . $direction);
        }
    }

    /**
     * @param Builder $query
     * @param DataTableFilters $filters
     * @param array $cachedNewIds
     * @param string $aliasedTableKey
     */
    public function addSubDataTableFilter(Builder $query, DataTableFilters $filters, array $cachedNewIds, string $aliasedTableKey)
    {
        if ( ! $this->hasParent($filters)) {
            return;
        }

        $key   = $this->getParentRelationKey($filters);
        $value = $this->getParentRelationValue($filters);

        $query->andWhere($key . ' = ' . $value);

        if ($filters->hasTempParentEditId()) {
            $query->inWhere($aliasedTableKey, $cachedNewIds);
        }
    }

    /**
     * @param DataTableFilters $filters
     * @return null|string
     */
    public function getParentRelationKey(DataTableFilters $filters): ?string
    {
        $model       = $filters->getParentModel();
        $relationKey = $filters->getParentRelationKey();

        if ( ! $model || ! $relationKey) {
            return null;
        }

        if ( ! $relation = $this->modelService->getRelation($model, $relationKey)) {
            return null;
        }

        if ($relation->getType() !== Relation::HAS_MANY) {
            return null;
        }

        if ( ! is_string($relation->getReferencedFields())) {
            return null;
        }

        return $relation->getReferencedFields();
    }

    /**
     * @param DataTableFilters $filters
     * @return mixed
     */
    public function getParentRelationValue(DataTableFilters $filters)
    {
        $model       = $filters->getParentModel();
        $editId      = $filters->getParentEditId();
        $relationKey = $filters->getParentRelationKey();

        if ($editId === 0) {
            return 0;
        }

        $relation     = $this->modelService->getRelation($model, $relationKey);
        $parentObject = $this->modelService->getObject($model, $editId);

        $field = $relation->getFields();

        return $parentObject->$field;
    }

    /**
     * @param DataTableFilters $filters
     * @return bool
     */
    public function hasParent(DataTableFilters $filters): bool
    {
        return $this->getParentRelationKey($filters) != null;
    }
}