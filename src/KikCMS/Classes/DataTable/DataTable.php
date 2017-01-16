<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\Phalcon\Paginator\QueryBuilder;
use KikCMS\Classes\WebForm\DataForm\DataForm;
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

    const JS_TRANSLATIONS = ['delete.confirmOne', 'delete.confirmMultiple', 'closeWarning'];

    /** @var DataForm */
    protected $form;

    /** @var array */
    protected $searchableFields = [];

    /** @var array */
    protected $fieldFormatting = [];

    /** @var StdClass */
    private $tableData;

    /**
     * Tracks whether the function 'initializeDatatable' has been run yet
     * @var bool
     */
    private $initialized;

    protected abstract function initialize();

    protected abstract function getModel(): string;

    /**
     * @return Builder
     */
    protected function getDefaultQuery()
    {
        $defaultQuery = new Builder();
        $defaultQuery->addFrom($this->getModel());

        return $defaultQuery;
    }

    /**
     * @return DataForm
     */
    public function getForm()
    {
        return $this->form;
    }

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
     * Initializes the dataTable
     */
    public function initializeDatatable()
    {
        if($this->initialized){
            return;
        }

        $instance = $this->getInstanceName();

        $this->form = new DataForm($this->getModel());
        $this->initialize();

        $this->session->set(self::SESSION_KEY, [$instance => [
            'class' => static::class
        ]]);

        $this->initialized = true;
    }

    /**
     * Renders the datatable
     *
     * @return string
     */
    public function render()
    {
        $this->initializeDatatable();
        $this->addAssets();

        return $this->renderView('index', [
            'tableData'       => $this->getTableData()->items->toArray(),
            'pagination'      => $this->getTableData(),
            'headerData'      => $this->getTableHeaderData(),
            'instanceName'    => $this->getInstanceName(),
            'isSearchable'    => count($this->searchableFields) > 0,
            'fieldFormatting' => $this->fieldFormatting,
            'self'            => $this,
        ]);
    }

    /**
     * @return Response
     */
    public function renderAddForm()
    {
        $this->initializeDatatable();

        $this->form->addHiddenField(self::INSTANCE, $this->getInstanceName());

        if ($this->form->isPosted()) {
            return $this->form->render();
        }

        return $this->form->render();
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

        return $this->form->renderWithData($id);
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
            'self'            => $this,
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
        return $this->view->getPartial('data-table/' . $viewName, $parameters);
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
            "page"     => $page,
            "limit"    => 100,
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
     * Retrieve the current editId from the DataForm
     *
     * @return mixed|null
     */
    public function getEditId()
    {
        if( ! $this->form->hasField(self::EDIT_ID)){
            return null;
        }

        return $this->form->getElement(self::EDIT_ID)->getValue();
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function getQuery(array $filters = []): Builder
    {
        $query = $this->getDefaultQuery();

        if (isset($filters[self::FILTER_SEARCH])) {
            $searchValue = $filters[self::FILTER_SEARCH];

            foreach ($this->searchableFields as $field) {
                $query->orWhere($field . ' LIKE "%' . $searchValue . '%"');
            }
        }

        if (isset($filters[self::FILTER_SORT_COLUMN])) {
            $column    = $filters[self::FILTER_SORT_COLUMN];
            $direction = $filters[self::FILTER_SORT_DIRECTION];
            $columns   = $this->getTableHeaderData();

            if (in_array($column, $columns) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($column . ' ' . $direction);
            }
        }

        return $query;
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
     * @return string
     */
    private function getTableSource(): string
    {
        $table = $this->getModel();

        /** @var Model $model */
        $model = new $table();
        return $model->getSource();
    }

    /**
     * Sets the js & css assets required
     */
    private function addAssets()
    {
        $this->view->assets->addJs('cmsassets/js/datatable/datatable.js');
        $this->view->assets->addCss('cmsassets/css/datatable.css');

        $this->form->addAssets();
    }
}