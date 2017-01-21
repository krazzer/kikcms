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
    const FILTER_PARENT_EDIT_ID = 'parentEditId';

    const JS_TRANSLATIONS = ['delete.confirmOne', 'delete.confirmMultiple', 'closeWarning'];

    /** @var DataForm */
    protected $form;

    /** @var array */
    protected $searchableFields = [];

    /** @var array */
    protected $fieldFormatting = [];

    /** @var string when using a DataTable in a DataTable, this key will be the reference to the parent table */
    protected $parentRelationKey;

    /** @var StdClass */
    private $tableData;

    /** @var string */
    private $cachedInstanceKey;

    /** @var int amount of rows shown on one page */
    private $limit = 100;

    /**
     * Tracks whether the function 'initializeDatatable' has been run yet
     * @var bool
     */
    private $initialized;

    protected abstract function initialize();

    public abstract function getModel(): string;

    /**
     * @return Builder
     */
    protected function getDefaultQuery()
    {
        $defaultQuery = new Builder();
        $defaultQuery->from($this->getModel());

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
     * Used by Twig
     *
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
     * @return string
     */
    public function getInstanceName()
    {
        if ( ! $this->cachedInstanceKey) {
            $this->cachedInstanceKey = uniqid('dataTable');
        }

        return $this->cachedInstanceKey;
    }

    /**
     * @return string
     */
    public function getParentRelationKey(): string
    {
        return $this->parentRelationKey;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return $this->parentRelationKey != null;
    }

    /**
     * Initializes the dataTable
     */
    public function initializeDatatable()
    {
        if ($this->initialized) {
            return;
        }

        $instance = $this->getInstanceName();

        $this->form = new DataForm($this->getModel());
        $this->initialize();

        $this->form->setIdentifier('form_' . $instance);

        if ($this->session->has(self::SESSION_KEY)) {
            $dataTableSessionData = $this->session->get(self::SESSION_KEY);
        } else {
            $dataTableSessionData = [];
        }

        $dataTableSessionData[$instance] = ['class' => static::class];

        $this->session->set(self::SESSION_KEY, $dataTableSessionData);

        $this->initialized = true;
    }

    /**
     * Renders the datatable
     *
     * @param array $filters
     * @return string
     */
    public function render(array $filters = [])
    {
        $this->initializeDatatable();
        $this->addAssets();

        return $this->renderView('index', [
            'tableData'       => $this->getTableData($filters)->items->toArray(),
            'pagination'      => $this->getTableData($filters),
            'headerData'      => $this->getTableHeaderData(),
            'instanceName'    => $this->getInstanceName(),
            'isSearchable'    => count($this->searchableFields) > 0,
            'fieldFormatting' => $this->fieldFormatting,
            'isAjax'          => $this->request->isAjax(),
            'self'            => $this,
        ]);
    }

    /**
     * @param int|null $parentEditId
     * @return Response
     */
    public function renderAddForm(int $parentEditId = null)
    {
        $this->initializeDatatable();

        $this->form->addHiddenField(self::INSTANCE, $this->getInstanceName());

        if ($this->parentRelationKey && $parentEditId !== null) {
            $this->form->addHiddenField($this->parentRelationKey, $parentEditId);
        }

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
            return $this->form->render([self::EDIT_ID => $id]);
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
     * Retrieve the current editId from the DataForm
     *
     * @return mixed|null
     */
    public function getEditId()
    {
        if ( ! $this->form->hasField(self::EDIT_ID)) {
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

        // add search
        if (isset($filters[self::FILTER_SEARCH])) {
            $searchValue = $filters[self::FILTER_SEARCH];

            foreach ($this->searchableFields as $field) {
                $query->orWhere($field . ' LIKE "%' . $searchValue . '%"');
            }
        }

        // add sort
        if (isset($filters[self::FILTER_SORT_COLUMN])) {
            $column    = $filters[self::FILTER_SORT_COLUMN];
            $direction = $filters[self::FILTER_SORT_DIRECTION];

            if (in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($column . ' ' . $direction);
            }
        }

        // add subDataTable filter
        if ($this->hasParent()) {
            $parentEditId = $filters[self::FILTER_PARENT_EDIT_ID];
            $query->andWhere($this->parentRelationKey . ' = ' . (int) $parentEditId);

            if ($parentEditId === 0) {
                $ids = $this->getCachedNewIds();
                $query->andWhere("pr.id IN ('" . implode("','", $ids) . "')");
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
     * @param string $instanceName
     */
    public function setInstanceName(string $instanceName)
    {
        $this->cachedInstanceKey = $instanceName;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return $this|DataTable
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Store the given id in cache, so we know which sub items' parentRelationKey needs to be updated after the parent is saved
     *
     * @param int $editId
     */
    public function cacheNewId(int $editId)
    {
        $cacheKey = $this->getNewIdsCacheKey();

        if ($this->cache->exists($cacheKey)) {
            $editKeys = $this->cache->get($cacheKey);
        } else {
            $editKeys = [];
        }

        $editKeys[] = $editId;

        $this->cache->save($cacheKey, $editKeys);
    }

    /**
     * @return array
     */
    public function getCachedNewIds()
    {
        $cacheKey = $this->getNewIdsCacheKey();

        if ( ! $this->cache->exists($cacheKey)) {
            return [];
        }

        return $this->cache->get($cacheKey);
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

    /**
     * @return string
     */
    private function getNewIdsCacheKey()
    {
        return $this->getInstanceName() . '-ids';
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
            "builder" => $this->getQuery($filters),
            "page"    => $page,
            "limit"   => $this->limit,
        ));

        $this->tableData = $paginator->getPaginate();

        return $this->tableData;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function getTableHeaderData(array $filters = []): array
    {
        if ( ! $this->getTableData($filters)->items->count()) {
            return [];
        }

        return array_keys($this->getTableData($filters)->items->getFirst()->toArray());
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
}