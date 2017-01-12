<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\Phalcon\Paginator\QueryBuilder;
use KikCMS\Classes\WebForm\DataForm;
use Phalcon\Di\Injectable;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder;
use stdClass;

abstract class DataTable extends Injectable
{
    const EDIT_ID     = 'dataTableEditId';
    const INSTANCE    = 'dataTableInstance';
    const PAGE        = 'dataTablePage';
    const SESSION_KEY = 'dataTable';

    const FILTER_SEARCH         = 'search';
    const FILTER_PAGE           = 'page';
    const FILTER_SORT_COLUMN    = 'sortColumn';
    const FILTER_SORT_DIRECTION = 'sortDirection';

    const JS_TRANSLATIONS = ['delete.confirmOne', 'delete.confirmMultiple'];

    /** @var DataForm */
    protected $form;

    /** @var array */
    protected $searchableFields = [];

    /** @var array */
    protected $fieldFormatting = [];

    /** @var Builder|null */
    private $query;

    /** @var StdClass */
    private $tableData;

    protected abstract function initialize();

    protected abstract function getTable(): string;

    /**
     * @param array $ids
     */
    public function delete(array $ids)
    {
        $this->db->delete($this->getTableSource(), "id IN (" . implode(',', $ids) . ")");
    }

    /**
     * @param string $column
     * @param string $value
     *
     * @return string
     */
    public function formatValue(string $column, $value)
    {
        return $this->fieldFormatting[$column]($value);
    }

    /**
     * Render the datatable
     *
     * @return string
     */
    public function render()
    {
        $this->initializeDatatable();

        return $this->renderView('index', [
            'tableData'       => $this->getTableData()->items->toArray(),
            'pagination'      => $this->getTableData(),
            'headerData'      => $this->getTableHeaderData(),
            'instanceName'    => $this->getInstanceName(),
            'isSearchable'    => count($this->searchableFields) > 0,
            'fieldFormatting' => $this->fieldFormatting,
            'this'            => $this,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function renderEditForm(int $id)
    {
        $this->initializeDatatable();

        $this->form->addHiddenField(self::EDIT_ID, $id);
        $this->form->addHiddenField(self::INSTANCE, $this->getInstanceName());

        if ($this->form->isPosted()) {
            return $this->form->render();
        }

        return $this->form->renderWithData($this->getEditData($id));
    }

    /**
     * @param int $page
     * @return Response
     */
    public function renderPagination(int $page = 1)
    {
        return $this->renderView('pagination', [
            'pagination' => $this->getTableData($page),
        ]);
    }

    /**
     * @param array $filters
     * @return Response
     */
    public function renderTable(array $filters = [])
    {
        $this->initializeDatatable();

        return $this->renderView('table', [
            'tableData'       => $this->getTableData($filters)->items->toArray(),
            'headerData'      => $this->getTableHeaderData(),
            'fieldFormatting' => $this->fieldFormatting,
            'filters'         => $filters,
            'this'            => $this,
        ]);
    }

    /**
     * Renders a view
     *
     * @param $viewName
     * @param array $parameters
     *
     * @return string
     */
    public function renderView($viewName, array $parameters = []): string
    {
        return $this->view->getRender('data-table', $viewName, $parameters);
    }

    /**
     * @param int $id
     * @return array
     */
    private function getEditData(int $id)
    {
        $query = new Builder();
        $query
            ->addFrom($this->getTable())
            ->andWhere('id = ' . $id);

        $data = $query->getQuery()->execute()->getFirst()->toArray();
        $data += $this->getDataStoredElseWhere($id);

        return $data;
    }

    /**
     * @return string
     */
    private function getInstanceName()
    {
        return 'dataTable' . str_replace('\\', '', static::class);
    }

    /**
     * @param $filters
     * @return stdClass
     */
    private function getTableData($filters = [])
    {
        if ($this->tableData) {
            return $this->tableData;
        }

        $page = (int) isset($filters[self::FILTER_PAGE]) ? $filters[self::FILTER_PAGE] : 1;

        $paginator = new QueryBuilder(array(
            "builder"  => $this->getQuery($filters),
            "limit"    => 100,
            "page"     => $page,
        ));

        $this->tableData = $paginator->getPaginate();

        return $this->tableData;
    }

    /**
     * @return array
     */
    private function getTableHeaderData(): array
    {
        if ( ! $this->getTableData()->items->count()) {
            return [];
        }

        return array_keys($this->getTableData()->items->getFirst()->toArray());
    }

    /**
     * Initializes the dataTable
     */
    private function initializeDatatable()
    {
        $instance = $this->getInstanceName();

        $this->form = new DataForm($this->getTable());
        $this->initialize();

        $this->session->set(self::SESSION_KEY, [$instance => [
            'class' => static::class
        ]]);
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function getQuery(array $filters = []): Builder
    {
        if ($this->query == null) {
            $this->query = new Builder();
            $this->query->addFrom($this->getTable());
        }

        if (isset($filters[self::FILTER_SEARCH])) {
            $searchValue = $filters[self::FILTER_SEARCH];

            foreach ($this->searchableFields as $field) {
                $this->query->orWhere($field . ' LIKE "%' . $searchValue . '%"');
            }
        }

        if (isset($filters[self::FILTER_SORT_COLUMN])) {
            $column    = $filters[self::FILTER_SORT_COLUMN];
            $direction = $filters[self::FILTER_SORT_DIRECTION];
            $columns   = $this->getTableHeaderData();

            if (in_array($column, $columns) && in_array($direction, ['asc', 'desc'])) {
                $this->query->orderBy($column . ' ' . $direction);
            }
        }

        return $this->query;
    }

    /**
     * @param Builder $query
     */
    public function setQuery(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @param string $column
     * @param callable $callback
     * @return $this|DataTable
     */
    public function setFieldFormatting(string $column, callable $callback)
    {
        $this->fieldFormatting[$column] = $callback;

        return $this;
    }

    /**
     * Retrieve data from fields that are not stored in the current DataTable's Table
     *
     * @param $id
     * @return array
     */
    private function getDataStoredElseWhere($id): array
    {
        $data = [];

        foreach ($this->form->getFields() as $key => $field) {
            if ($field->isStoredElsewhere()) {
                $data[$key] = $field->getValue($id);
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getTableSource(): string
    {
        $table = $this->getTable();

        /** @var Model $model */
        $model = new $table();
        return $model->getSource();
    }
}