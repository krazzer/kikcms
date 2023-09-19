<?php declare(strict_types=1);


namespace KikCMS\Services\DataTable;


use KikCMS\Classes\DataTable\DataTableFilters;
use KikCMS\Classes\DataTable\Filter\Filter;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Services\ModelService;
use KikCmsCore\Config\DbConfig;
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
    public function addCustomFilters(Builder $query, DataTableFilters $filters, array $customFilters): void
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
    public function addSearchFilter(Builder $query, DataTableFilters $filters, array $searchFields): void
    {
        if ( ! $searchValue = $filters->getSearch()) {
            return;
        }

        $searchConditions = [];
        $bindParams       = [];

        foreach ($searchFields as $index => $field) {
            $searchConditions[] = $field . ' LIKE :searchValue' . $index . ':';
            $bindParams['searchValue' . $index] = '%' . $searchValue . '%';
        }

        $query->andWhere(implode(' OR ', $searchConditions), $bindParams);
    }

    /**
     * @param Builder $query
     * @param DataTableFilters $filters
     * @param bool $isSortable
     * @param string $sortableField
     */
    public function addSortFilter(Builder $query, DataTableFilters $filters, bool $isSortable, string $sortableField): void
    {
        if ( ! $filters->getSortColumn()) {
            if ($isSortable) {
                $query->orderBy($sortableField . ' ' . DbConfig::SQL_SORT_ASCENDING);
            }

            return;
        }

        $column    = $filters->getSortColumn();
        $direction = $filters->getSortDirection();

        // if an alias is used, use the non-alias for ordering
        if (is_array($query->getColumns())) {
            foreach ($query->getColumns() as $queryColumn) {
                $parts = preg_split('/ as /i', $queryColumn);

                if (count($parts) == 2 && $parts[1] == $column) {
                    $column = $parts[0];
                }
            }
        }

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
    public function addSubDataTableFilter(Builder $query, DataTableFilters $filters, array $cachedNewIds,
                                          string $aliasedTableKey): void
    {
        if ( ! $this->hasParent($filters)) {
            return;
        }

        $key   = $this->getParentRelationField($filters);
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
    public function getParentRelationField(DataTableFilters $filters): ?string
    {
        $model       = $filters->getParentModel();
        $relationKey = $filters->getParentRelationKey();

        if ( ! $model || ! $relationKey) {
            return null;
        }

        list($model, $relationKey) = $this->relationKeyService->getLastModelAndKey($model, $relationKey);

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
    public function getParentRelationValue(DataTableFilters $filters): mixed
    {
        $model       = $filters->getParentModel();
        $editId      = $filters->getParentEditId();
        $relationKey = $filters->getParentRelationKey();

        if ($editId === 0) {
            return 0;
        }

        $parentObject  = $this->modelService->getObject($model, $editId);
        $relatedObject = $this->relationKeyService->getLastRelatedObject($parentObject, $relationKey);

        list($model, $relationKey) = $this->relationKeyService->getLastModelAndKey($model, $relationKey);

        $relation = $this->modelService->getRelation($model, $relationKey);
        $field    = $relation->getFields();

        return $relatedObject->$field;
    }

    /**
     * @param DataTableFilters $filters
     * @return bool
     */
    public function hasParent(DataTableFilters $filters): bool
    {
        return $this->getParentRelationField($filters) != null;
    }
}