<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\DbService;
use KikCMS\Classes\Phalcon\Paginator\QueryBuilder;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Services\LanguageService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Tag;

/**
 * @property DbService $dbService;
 * @property LanguageService $languageService;
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
        'dataTable.switchWarning',
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

    /** @var bool */
    protected $multiLingual = false;

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

    /** @var TableData */
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
     * @param array $rowData
     *
     * @return string
     */
    public function formatValue(string $column, $value, array $rowData = [])
    {
        return $this->fieldFormatting[$column]($value, $rowData);
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
        /** @var DataTableFilters $filters */
        $filters = parent::getFilters();

        // make sure language code is default when empty
        if ( ! $filters->getLanguageCode()) {
            $filters->setLanguageCode($this->languageService->getDefaultLanguageCode());
        }

        return $filters;
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

        $instance     = $this->getInstance();
        $formClass    = $this->getFormClass();
        $editId = $this->getFilters()->getEditId();
        $languageCode = $this->getFilters()->getLanguageCode();

        /** @var DataForm $dataForm */
        $dataForm = new $formClass();
        $dataForm->getFilters()->setEditId($editId);
        $dataForm->getFilters()->setLanguageCode($languageCode);
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
            'tableData'       => $this->getTableData(),
            'jsData'          => $this->getJsData(),
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
            'tableData' => $this->getTableData(),
        ]);
    }

    /**
     * @return string
     */
    public function renderTable()
    {
        $this->initializeDatatable();

        return $this->view->getPartial($this->tableView, [
            'tableData'       => $this->getTableData(),
            'fieldFormatting' => $this->fieldFormatting,
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
            'tabs'            => $this->form->getTabs(),
            'currentTab'      => $this->form->getCurrentTab(),
            'multiLingual'    => $this->multiLingual,
            'currentLanguage' => $this->getFilters()->getLanguageCode(),
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
     * Get a map of fields that are shown in the table header, where the key corresponds to the query result key
     * e.g. ['name' => 'Name', 'category_name' => 'Category']
     *
     * @return array
     */
    protected function getTableFieldMap(): array
    {
        return [];
    }

    /**
     * @return string
     */
    private function getNewIdsCacheKey()
    {
        return $this->getInstance() . '-ids';
    }

    /**
     * @return TableData
     */
    private function getTableData(): TableData
    {
        if ($this->tableData) {
            return $this->tableData;
        }

        $paginate = (new QueryBuilder([
            "builder" => $this->getQuery(),
            "page"    => $this->filters->getPage(),
            "limit"   => $this->limit,
        ]))->getPaginate();

        $this->tableData = (new TableData())
            ->setPages($paginate->pages)
            ->setLimit($paginate->limit)
            ->setCurrent($paginate->current)
            ->setTotalItems($paginate->total_items)
            ->setTotalPages($paginate->total_pages)
            ->setDisplayMap($this->getTableFieldMap())
            ->setData($paginate->items->toArray());

        return $this->tableData;
    }
}