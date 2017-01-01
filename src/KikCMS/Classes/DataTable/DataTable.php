<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\DbWrapper;
use KikCMS\Classes\WebForm\DataForm;
use Phalcon\Di\Injectable;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Adapter\QueryBuilder;
use stdClass;

/** @property DbWrapper $dbWrapper */
abstract class DataTable extends Injectable
{
    const EDIT_ID     = 'dataTableId';
    const INSTANCE    = 'dataTableInstance';
    const SESSION_KEY = 'dataTable';

    /** @var DataForm */
    protected $form;

    /** @var array */
    protected $searchableFields = [];

    /** @var Builder|null */
    private $query;

    /** @var StdClass */
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

        return $this->renderView('index', [
            'tableData'    => $this->getTableData()->items->toArray(),
            'pagination'   => $this->getTableData(),
            'headerData'   => $this->getTableHeaderData(),
            'instanceName' => $this->getInstanceName(),
            'isSearchable' => count($this->searchableFields) > 0,
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
     * @param int $page
     * @return Response
     */
    public function renderTable(int $page = 1)
    {
        $this->initializeDatatable();

        return $this->renderView('table', [
            'tableData'  => $this->getTableData($page)->items->toArray(),
            'headerData' => $this->getTableHeaderData(),
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

        return $query->getQuery()->execute()->getFirst()->toArray();
    }

    /**
     * @return string
     */
    private function getInstanceName()
    {
        return 'dataTable' . str_replace('\\', '', static::class);
    }

    /**
     * @param int $page
     * @return stdClass
     */
    private function getTableData(int $page = 1)
    {
        if ($this->tableData) {
            return $this->tableData;
        }

        $paginator = new QueryBuilder(array(
            "builder" => $this->getQuery(),
            "limit"   => 100,
            "page"    => $page
        ));

        // todo: put this in custom paginator
        $page = $paginator->getPaginate();

        $pages = [];

        if ($page->last <= 6) {
            for ($i = 1; $i <= $page->last; $i++) {
                $pages[$i] = $i;
            }
        } else {
            if ($page->current < 5) {
                $pages = [1, 2, 3, 4, 5, null, $page->last];
            } elseif($page->current > $page->last - 4) {
                $pages = [1, null, $page->last - 4, $page->last - 3, $page->last - 2, $page->last - 1, $page->last];
            } else {
                $pages = [1, null, $page->current - 1, $page->current, $page->current + 1, null, $page->last];
            }
        }

        $page->pages = $pages;
        $this->tableData = $page;

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

        $this->query = new Builder();
        $this->query->addFrom($this->getTable());

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