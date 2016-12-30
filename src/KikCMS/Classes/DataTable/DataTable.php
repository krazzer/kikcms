<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\DbWrapper;
use KikCMS\Classes\WebForm\DataForm;
use KikCMS\Models\DummyProducts;
use Phalcon\Di\Injectable;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Adapter\QueryBuilder;

/** @property DbWrapper $dbWrapper */
abstract class DataTable extends Injectable
{
    const EDIT_ID     = 'dataTableId';
    const INSTANCE    = 'dataTableInstance';
    const SESSION_KEY = 'dataTable';

    /** @var DataForm */
    protected $form;

    /** @var Builder|null */
    private $query;

    /** @var array */
    private $tableData;

    protected abstract function initialize();

    protected abstract function getTable(): string;

    /**
     * Render the datatable
     *
     * @return string
     */
    public function render()
    {
        $this->initializeDatatable();

        return $this->renderView('table', [
            'tableData'    => $this->getTableData()->items->toArray(),
            'headerData'   => $this->getTableHeaderData(),
            'instanceName' => $this->getInstanceName(),
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

        return $this->form->renderWithData($this->getEditData($id));
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
        return $this->view->getRender('datatable', $viewName, $parameters);
    }

    /**
     * @param int $id
     * @return array
     */
    private function getEditData(int $id)
    {
        $editData = $this->dbWrapper->queryRow("
            SELECT * FROM " . $this->getTable() . " 
            WHERE id = " . $id
        );

        return $editData;
    }

    /**
     * @return string
     */
    private function getInstanceName()
    {
        return 'dataTable' . str_replace('\\', '', static::class);
    }

    /**
     * @return array
     */
    private function getTableData()
    {
        if ($this->tableData) {
            return $this->tableData;
        }

        $paginator = new QueryBuilder(array(
            "builder" => $this->getQuery(),
            "limit"   => 100,
            "page"    => 1
        ));

        $this->tableData = $paginator->getPaginate();

        return $this->tableData;
    }

    /**
     * @return array|null
     */
    private function getTableHeaderData()
    {
        $tableData = $this->getTableData()->items->getFirst()->toArray();

        if ( ! $tableData) {
            return null;
        } else {
            return array_keys($tableData);
        }
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
     * @return Builder
     */
    public function getQuery(): Builder
    {
        if ($this->query != null) {
            return $this->query;
        }


        $query = new Builder();
        $query->addFrom(DummyProducts::class);

        $this->query = $query;
            //$this->modelsManager->createBuilder()->from($this->getTable());

        return $this->query;
    }

    /**
     * @param Builder $query
     */
    public function setQuery(Builder $query)
    {
        $this->query = $query;
    }
}