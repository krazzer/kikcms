<?php declare(strict_types=1);

namespace KikCMS\Classes\DataTable;

use Exception;
use KikCMS\Classes\Renderable\Filters;
use Phalcon\Mvc\Model\Query\Builder;


/**
 * A DataTable used for selecting it's results
 */
abstract class SelectDataTable extends DataTable
{
    /** @inheritdoc */
    public $tableView = 'datatables/select/table';

    /** @inheritdoc */
    public $indexView = 'datatables/select/index';

    /** @inheritdoc */
    public $jsClass = 'SelectDataTable';

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return '';
    }

    /**
     * @return Filters|SelectDataTableFilters
     */
    public function getEmptyFilters(): Filters
    {
        return new SelectDataTableFilters();
    }

    /**
     * @return Filters|SelectDataTableFilters
     */
    public function getFilters(): Filters
    {
        return parent::getFilters();
    }

    /**
     * Update the given query so it adds a column that is 1 if the row is selected, 0 if it's not
     * It will then add a orderBy so it will be ordered by the new field descending.
     *
     * By doing so the selected items will be shown first in the DataTable
     *
     * @param Builder $query
     */
    public function setQueryToShowSelectionFirst(Builder $query)
    {
        if( ! $columns = $query->getColumns()){
            throw new Exception('A SelectDataTable must have columns in its query');
        }

        $selectedIds = $this->getFilters()->getSelectedValues();
        $field       = $this->getAliasedTableKey();

        if ($selectedIds) {
            $selectedColumn = 'IF(' . $field . ' IN(' . implode(',', $selectedIds) . '), 1, 0) AS dataTableSelectIds';
        } else {
            $selectedColumn = '0 AS dataTableSelectIds';
        }

        $query->columns(array_merge($columns, [$selectedColumn]));

        if (is_array($query->getOrderBy())) {
            $query->orderBy(array_merge(['dataTableSelectIds DESC'], $query->getOrderBy()));
        } else {
            $query->orderBy('dataTableSelectIds DESC, ' . $query->getOrderBy());
        }
    }
}