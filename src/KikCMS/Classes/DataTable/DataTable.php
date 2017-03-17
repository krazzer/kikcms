<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\DbService;
use KikCMS\Classes\Phalcon\Paginator\QueryBuilder;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Tag;
use stdClass;

/**
 * @property DbService $dbService;
 */
abstract class DataTable extends Renderable
{
    const EDIT_ID     = 'editId';
    const INSTANCE    = 'dataTableInstance';
    const PAGE        = 'dataTablePage';
    const SESSION_KEY = 'dataTable';

    const JS_TRANSLATIONS = [
        'dataTable.delete.confirmOne',
        'dataTable.delete.confirm',
        'dataTable.closeWarning',
    ];

    /** @var DataForm */
    protected $form;

    /** @var DataTableFilters */
    protected $filters;

    /** @var string */
    protected $instancePrefix = 'dataTable';

    /** @var array */
    protected $searchableFields = [];

    /** @var array assoc that contains the column name as key, and the value that will be used for sorting in the query
     * i.e. [id => p.id] will make sure the id column is sorted by p.id, this must be used to avoid ambiguity  */
    protected $orderableFields = [];

    /** @var array assoc column as key, callable as value, that will be used to format a value in the result table */
    protected $fieldFormatting = [];

    /** @var string when using a DataTable in a DataTable, this key will be the reference to the parent table */
    protected $parentRelationKey;

    /** @var string */
    protected $viewDirectory = 'datatable';

    /** @var string */
    protected $jsClass = 'DataTable';

    /** @var bool if you're fairly certain the user will use a wysiwyg editor, set this to true to preload the js
     * note that if you don't the editor will be loaded dynamically, but will load a bit slower */
    protected $preLoadWysiwygJs = false;

    /** @var bool */
    protected $sortable = false;

    /** @var string */
    protected $orderField = 'display_order';

    /** @var string */
    public $indexView = 'datatable/index';

    /** @var string */
    public $tableView = 'datatable/table';

    /** @var StdClass */
    private $tableData;

    /** @var int amount of rows shown on one page */
    private $limit = 100;

    /**
     * Tracks whether the function 'initializeDatatable' has been run yet
     * @var bool
     */
    private $initialized;

    public abstract function getModel(): string;

    public abstract function getFormClass(): string;

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
     * @param array $ids
     */
    public function delete(array $ids)
    {
        $this->dbService->delete($this->getModel(), ['id' => $ids]);
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatFinderImage($value): string
    {
        if ( ! $value) {
            return '';
        }

        $thumbUrl = $this->url->get('finderFileThumb', $value);
        $url      = $this->url->get('finderFile', $value);

        $style = 'background-image: url(' . $thumbUrl . ')';

        return Tag::tagHtml('div', [
            'class'          => 'thumb',
            'data-url'       => $url,
            'data-thumb-url' => $thumbUrl,
            'style'          => $style
        ]);
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
     * Get the table's key with alias if present, used for queries. i.e. p.id
     *
     * @return string
     */
    public function getAliasedTableKey(): string
    {
        $alias = $this->dbService->getAliasForModel($this->getModel());

        if ( ! $alias) {
            return 'id';
        }

        return $alias . '.id';
    }

    /**
     * @return Filters|DataTableFilters
     */
    public function getEmptyFilters(): Filters
    {
        return new DataTableFilters();
    }

    /**
     * @return Filters|DataTableFilters
     */
    public function getFilters(): Filters
    {
        return parent::getFilters();
    }

    /**
     * @return DataForm
     */
    public function getForm(): DataForm
    {
        return $this->form;
    }

    /**
     * Returns translation container, with labels for add, edit, delete and deleteOne
     *
     * @return string
     */
    public function getLabels(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getParentRelationKey(): string
    {
        return $this->parentRelationKey;
    }

    /**
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }

    /**
     * @return array
     */
    public function getOrderableFields(): array
    {
        return $this->orderableFields;
    }

    /**
     * @return string
     */
    public function getOrderField(): string
    {
        return $this->orderField;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
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

        $instance  = $this->getInstance();
        $formClass = $this->getFormClass();
        $editId    = $this->filters->getEditId();

        /** @var DataForm $dataForm */
        $dataForm = new $formClass();
        $dataForm->getFilters()->setEditId($editId);
        $dataForm->initializeForm();

        $this->form = $dataForm;
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
     * @inheritdoc
     */
    public function render(): string
    {
        $this->initializeDatatable();
        $this->addAssets();

        return $this->view->getPartial($this->indexView, [
            'tableData'       => $this->getTableData()->items->toArray(),
            'pagination'      => $this->getTableData(),
            'headerData'      => $this->getTableHeaderData(),
            'instanceName'    => $this->getInstance(),
            'labels'          => $this->getLabels(),
            'jsData'          => $this->getJsData(),
            'parentEditId'    => $this->filters->getParentEditId(),
            'isSearchable'    => count($this->searchableFields) > 0,
            'fieldFormatting' => $this->fieldFormatting,
            'jsClass'         => $this->jsClass,
            'sortable'        => $this->sortable,
            'self'            => $this,
        ]);
    }

    /**
     * @return Response
     */
    public function renderAddForm()
    {
        $this->initializeDatatable();

        $this->form->addHiddenField(self::INSTANCE, $this->getInstance());

        if ($this->parentRelationKey && $this->filters->getParentEditId() !== null) {
            $this->form->addHiddenField($this->parentRelationKey, $this->filters->getParentEditId());
        }

        if ($this->form->isPosted()) {
            return $this->form->render();
        }

        return $this->form->render();
    }

    /**
     * @return Response
     */
    public function renderEditForm()
    {
        $this->initializeDatatable();

        $this->form->addHiddenField(self::EDIT_ID, $this->filters->getEditId());
        $this->form->addHiddenField(self::INSTANCE, $this->getInstance());

        if ($this->form->isPosted()) {
            return $this->form->render();
        }

        return $this->form->renderWithData();
    }

    /**
     * @return string
     */
    public function renderPagination()
    {
        return $this->renderView('pagination', [
            'pagination' => $this->getTableData(),
        ]);
    }

    /**
     * @return string
     */
    public function renderTable()
    {
        $this->initializeDatatable();

        return $this->view->getPartial($this->tableView, [
            'tableData'       => $this->getTableData()->items->toArray(),
            'headerData'      => $this->getTableHeaderData(),
            'fieldFormatting' => $this->fieldFormatting,
            'filters'         => $this->filters,
            'sortable'        => $this->sortable,
            'self'            => $this,
        ]);
    }

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        $queryBuilder = new FilterQueryBuilder($this, $this->filters);
        $query        = $this->getDefaultQuery();

        return $queryBuilder->getQuery($query);
    }

    /**
     * @param string $column
     * @param callable $callback
     * @return $this|DataTable
     */
    public function setFieldFormatting(string $column, $callback)
    {
        $this->fieldFormatting[$column] = $callback;

        return $this;
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
     * Store the given id in cache, so we know which sub items' parentRelationKey
     * needs to be updated after the parent is saved
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
     * @param string $template
     * @return string
     */
    public function renderWindow(string $template)
    {
        return $this->renderView($template, [
            'tabs'       => $this->form->getTabs(),
            'currentTab' => $this->form->getCurrentTab(),
        ]);
    }

    /**
     * Sets the js & css assets required
     */
    protected function addAssets()
    {
        $this->view->assets->addJs('cmsassets/js/datatable/datatable.js');
        $this->view->assets->addCss('cmsassets/css/toolbarComponent.css');
        $this->view->assets->addCss('cmsassets/css/datatable.css');

        if ($this->sortable) {
            $this->view->assets->addJs('cmsassets/js/datatable/sortControl.js');
        }

        if ($this->preLoadWysiwygJs) {
            $this->view->assets->addJs('//cdn.tinymce.com/4/tinymce.min.js');
        }

        $translations = DataTable::JS_TRANSLATIONS;

        if ($this->getLabels()) {
            $translations[] = $this->getLabels() . '.add';
            $translations[] = $this->getLabels() . '.edit';
            $translations[] = $this->getLabels() . '.delete';
            $translations[] = $this->getLabels() . '.deleteOne';
        }

        $this->view->jsTranslations = array_merge($this->view->jsTranslations, $translations);
    }

    /**
     * @inheritdoc
     */
    protected function getJsProperties(): array
    {
        return [
            'parentEditId' => $this->filters->getParentEditId(),
            'labels'       => $this->getLabels(),
        ];
    }

    /**
     * @return string
     */
    private function getNewIdsCacheKey()
    {
        return $this->getInstance() . '-ids';
    }

    /**
     * @return stdClass
     */
    private function getTableData()
    {
        if ($this->tableData) {
            return $this->tableData;
        }

        $paginator = new QueryBuilder(array(
            "builder" => $this->getQuery(),
            "page"    => $this->filters->getPage(),
            "limit"   => $this->limit,
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
}