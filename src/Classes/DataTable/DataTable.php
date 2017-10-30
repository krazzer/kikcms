<?php

namespace KikCMS\Classes\DataTable;


use KikCMS\Classes\DataTable\Filter\Filter;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\Paginator\QueryBuilder;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\FieldStorageService;
use KikCMS\Services\LanguageService;
use Phalcon\Cache\Backend;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Tag;

/**
 * @property DbService $dbService
 * @property LanguageService $languageService
 * @property Backend $diskCache
 * @property Translator $translator
 * @property FieldStorageService $fieldStorageService
 * @property AccessControl $acl
 */
abstract class DataTable extends Renderable
{
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

    /** @var Filter[] */
    protected $customFilters = [];

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

    /** @var string|null column that is referenced in the parent table, if left null, it will use 'id' by default */
    protected $parentRelationReferenceKey;

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

    /** @var bool if true, newly added items will be added on top */
    protected $sortableNewFirst = false;

    /** @var string */
    protected $sortableField = 'display_order';

    /** @var string */
    public $indexView = 'datatable/index';

    /** @var TableButton[] */
    public $tableButtons = [];

    /** @var string */
    public $tableView = 'datatable/table';

    /** @var TableData */
    private $tableData;

    /** @var int amount of rows shown on one page */
    private $limit = 100;

    /** @var Rearranger */
    private $rearranger;

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
        $alias = $this->dbService->getAliasForModel($this->getModel());

        $defaultQuery = new Builder();

        if ($alias) {
            $defaultQuery->from([$alias => $this->getModel()]);
        } else {
            $defaultQuery->from($this->getModel());
        }

        return $defaultQuery;
    }

    /**
     * @param Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        $this->customFilters[$filter->getField()] = $filter;
    }

    /**
     * @return bool
     */
    public function canAdd(): bool
    {
        if ( ! $this->acl->resourceExists(static::class)) {
            return true;
        }

        return $this->acl->allowed(static::class, Permission::ACCESS_ADD);
    }

    /**
     * @param null $id
     * @return bool
     */
    public function canDelete($id = null): bool
    {
        if ( ! $this->acl->resourceExists(static::class)) {
            return true;
        }

        return $this->acl->allowed(static::class, Permission::ACCESS_DELETE, ['id' => $id]);
    }

    /**
     * @param int|null $id
     * @return bool
     */
    public function canEdit($id = null): bool
    {
        if ( ! $this->acl->resourceExists(static::class)) {
            return true;
        }

        return $this->acl->allowed(static::class, Permission::ACCESS_EDIT, ['id' => $id]);
    }

    /**
     * @param $id
     * @param $column
     * @param $checked
     * @return bool
     */
    public function checkCheckbox($id, $column, $checked): bool
    {
        $this->renderEditForm();

        $form     = $this->getForm();
        $field    = $form->getFieldMap()->get($column);
        $langCode = $this->getFilters()->getLanguageCode();
        $editData = $this->dbService->getTableRowById($this->getModel(), $id);

        if ($field->getStorage()) {
            return $this->fieldStorageService->store($field, $checked, $id, $editData, $langCode);
        }

        return $this->dbService->update($this->getModel(), [$column => $checked], ['id' => $id]);
    }

    /**
     * @param array $ids
     */
    public function delete(array $ids)
    {
        foreach ($ids as $i => $id){
            if( ! $this->canDelete($id)){
                unset($ids[$i]);
            }
        }

        $this->dbService->delete($this->getModel(), ['id' => $ids]);
    }

    /**
     * @return null|string
     */
    public function getParentRelationKeyReference(): ?string
    {
        return $this->parentRelationReferenceKey;
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatBoolean($value): string
    {
        return $this->translator->tl('global.' . ($value ? 'yes' : 'no'));
    }

    /**
     * @param $value
     * @param $rowData
     * @param $column
     * @return string
     */
    protected function formatCheckbox($value, $rowData, $column)
    {
        $attributes = [
            'type'     => 'checkbox',
            'class'    => 'table-checkbox',
            'data-col' => $column,
        ];

        if ($rowData[$column] && $value) {
            $attributes['checked'] = 'checked';
        }

        return Tag::tagHtml('input', $attributes);
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
    public function formatValue(string $column, $value, array $rowData = []): string
    {
        return $this->fieldFormatting[$column]($value, $rowData, $column);
    }

    /**
     * Get the table's default alias if present
     *
     * @return null|string
     */
    public function getAlias(): ?string
    {
        return $this->dbService->getAliasForModel($this->getModel());
    }

    /**
     * Get the table's key with alias if present, used for queries. i.e. p.id
     *
     * @return string
     */
    public function getAliasedTableKey(): string
    {
        if ( ! $alias = $this->getAlias()) {
            return 'id';
        }

        return $alias . '.id';
    }

    /**
     * @return Filter[]
     */
    public function getCustomFilters(): array
    {
        return $this->customFilters;
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
     * Returns array with 2 values, singular and plural, e.g. ["item", "items"]
     *
     * @return array
     */
    public function getLabels(): array
    {
        return [
            $this->translator->tl('dataTables.default.singular'),
            $this->translator->tl('dataTables.default.plural')
        ];
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return null|string
     */
    public function getParentRelationKey(): ?string
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
    public function getSortableField(): string
    {
        return $this->sortableField;
    }

    /**
     * @return Rearranger
     */
    public function getRearranger(): Rearranger
    {
        if ( ! $this->rearranger) {
            $this->rearranger = new Rearranger($this);
        }

        return $this->rearranger;
    }

    /**
     * @return bool
     */
    public function isMultiLingual(): bool
    {
        return $this->multiLingual;
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
    public function isSortableNewFirst(): bool
    {
        return $this->sortableNewFirst;
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
     * @param bool $initializeForm
     */
    public function initializeDatatable($initializeForm = false)
    {
        if ($this->initialized) {
            return;
        }

        if ($initializeForm) {
            $instance     = $this->getInstance();
            $formClass    = $this->getFormClass();
            $editId       = $this->getFilters()->getEditId();
            $languageCode = $this->getFilters()->getWindowLanguageCode();
            $parentEditId = $this->getFilters()->getParentEditId();

            /** @var DataForm $dataForm */
            $dataForm = new $formClass();

            $dataForm
                ->setDataTable($this)
                ->getFilters()
                ->setEditId($editId)
                ->setLanguageCode($languageCode)
                ->setParentEditId($parentEditId);

            $this->form = $dataForm;
            $this->form->setIdentifier('form_' . $instance);
        }

        $this->initialize();

        $this->initialized = true;
    }

    /**
     * @inheritdoc
     */
    public function render(): string
    {
        if ($this->acl->resourceExists(static::class) && ! $this->acl->allowed(static::class)) {
            return 'unauthorized';
        }

        $this->initializeDatatable();
        $this->addAssets();

        return $this->view->getPartial($this->indexView, [
            'tableData'       => $this->getTableData(),
            'jsData'          => $this->getJsData(),
            'editLabel'       => $this->getEditLabel(),
            'currentLangCode' => $this->getFilters()->getLanguageCode(),
            'languages'       => $this->languageService->getLanguages(),
            'sortLabel'       => $this->translator->tl('dataTable.sort'),
            'fieldFormatting' => $this->fieldFormatting,
            'canAdd'          => $this->canAdd(),
            'canEdit'         => $this->canEdit(),
            'canDelete'       => $this->canDelete(),
            'self'            => $this,
        ]);
    }

    /**
     * @return Response
     */
    public function renderAddForm()
    {
        $this->initializeDatatable(true);

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
        $this->initializeDatatable(true);

        $this->form->addHiddenField(self::INSTANCE, $this->getInstance());

        return $this->form->render();
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
            'editLabel'       => $this->getEditLabel(),
            'sortLabel'       => $this->translator->tl('dataTable.sort'),
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

        if ($this->diskCache->exists($cacheKey)) {
            $editKeys = $this->diskCache->get($cacheKey);
        } else {
            $editKeys = [];
        }

        $editKeys[] = $editId;

        $this->diskCache->save($cacheKey, $editKeys);
    }

    /**
     * @return array
     */
    public function getCachedNewIds()
    {
        $cacheKey = $this->getNewIdsCacheKey();

        if ( ! $this->diskCache->exists($cacheKey)) {
            return [];
        }

        return $this->diskCache->get($cacheKey);
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
            'multiLingual'    => $this->isMultiLingual(),
            'currentLangCode' => $this->getFilters()->getWindowLanguageCode(),
            'canEdit'         => $this->canEdit($this->form->getFilters()->getEditId()),
            'languages'       => $this->languageService->getLanguages(),
        ]);
    }

    /**
     * Sets the js & css assets required
     */
    protected function addAssets()
    {
        if ($this->preLoadWysiwygJs) {
            $this->view->assets->addJs('//cdn.tinymce.com/4/tinymce.min.js');
        }

        $this->view->jsTranslations = array_merge($this->view->jsTranslations, DataTable::JS_TRANSLATIONS);
    }

    /**
     * @param string $icon
     * @param string $title
     * @param string $class
     * @param string|null $url
     */
    protected function addTableButton(string $icon, string $title, string $class, string $url = null)
    {
        $this->tableButtons[] = new TableButton($icon, $title, $class, $url);
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
     * @return TableData
     */
    protected function getTableData(): TableData
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
    private function getEditLabel(): string
    {
        return ucfirst($this->translator->tl('dataTable.edit', ['itemSingular' => $this->getLabels()[0]]));
    }

    /**
     * @return string
     */
    private function getNewIdsCacheKey()
    {
        return $this->getInstance() . '-ids';
    }
}