<?php declare(strict_types=1);


namespace KikCMS\Services\DataTable;


use KikCMS\Classes\DataTable\TableData;
use KikCMS\Services\Util\PaginateListService;
use KikCMS\Services\Util\QueryService;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Adapter\QueryBuilder;

/**
 * Handles TableData objects for DataTables
 *
 * @property QueryService $queryService
 * @property PaginateListService $paginateListService
 */
class TableDataService extends Injectable
{
    /**
     * @param Builder $query
     * @param int $page
     * @param int $limit
     * @param array $fieldMap
     * @return TableData
     */
    public function getTableData(Builder $query, int $page, int $limit, array $fieldMap): TableData
    {
        $paginate = (new QueryBuilder([
            "builder" => $query,
            "page"    => $page,
            "limit"   => $limit,
        ]))->getPaginate();

        $paginate->pages = $this->paginateListService->getPageList($paginate->last, $paginate->current);

        $tableData = $paginate->items->toArray();

        foreach ($tableData as &$row) {
            $row = (array) $row;
        }

        $aliases = $this->queryService->getAliases($query);

        $headColumns = $this->getHeadColumns($tableData, $fieldMap, $aliases, $query);

        $this->tableData = (new TableData())
            ->setPages($paginate->pages)
            ->setLimit($paginate->limit)
            ->setCurrent($paginate->current)
            ->setTotalItems($paginate->total_items)
            ->setTotalPages($paginate->total_pages)
            ->setDisplayMap($fieldMap)
            ->setTableHeadColumns($headColumns)
            ->setData($tableData);

        return $this->tableData;
    }

    /**
     * @param array $tableData
     * @param array $fieldMap
     * @param array $aliases
     * @param Builder $query
     * @return array
     */
    public function getHeadColumns(array $tableData, array $fieldMap, array $aliases, Builder $query): array
    {
        $queryColumns = $query->getColumns();
        $headColumns  = $this->getHeadColumnsRaw($tableData, $fieldMap);

        if ( ! $queryColumns) {
            return $headColumns;
        }

        foreach ($headColumns as $column => $name) {
            foreach ($aliases as $alias) {
                $aliasedColumn = $alias . '.' . $column;

                if ( ! in_array($aliasedColumn, $queryColumns)) {
                    continue;
                }

                $headColumns = array_change_key($headColumns, $column, $aliasedColumn);
            }
        }

        return $headColumns;
    }


    /**
     * @param array $tableData
     * @param array $fieldMap
     * @return array
     */
    private function getHeadColumnsRaw(array $tableData, array $fieldMap): array
    {
        if ($fieldMap) {
            return $fieldMap;
        }

        if ( ! $tableData) {
            return [];
        }

        return array_combine(array_keys($tableData[0]), array_keys($tableData[0]));
    }
}