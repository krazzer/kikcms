<?php

namespace KikCMS\Classes\DataTable;


use Exception;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\DataTable\Filter\Filter;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Phalcon\SecuritySingleToken;
use KikCMS\Services\DataTable\TableDataService;
use KikCMS\Services\ModelService;
use KikCMS\Services\Util\QueryService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Services\LanguageService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Relation;

/**
 * @property AccessControl $acl
 * @property DbService $dbService
 * @property KeyValue $keyValue
 * @property LanguageService $languageService
 * @property QueryService $queryService
 * @property ModelService $modelService
 * @property RelationKeyService $relationKeyService
 * @property SecuritySingleToken $securitySingleToken
 * @property TableDataService $tableDataService
 * @property Translator $translator
 */
abstract class DataTable extends Renderable
{
    const PAGE             = 'dataTablePage';
    const SESSION_KEY      = 'dataTable';
    const INSTANCE_PREFIX  = 'dataTable';
    const IDS_CACHE_SUFFIX = 'ids';
    const TABLE_KEY        = 'id';

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
    protected $instancePrefix = self::INSTANCE_PREFIX;

    /** @var array */
    protected $searchableFields = [];

    /** @var array assoc column as key, callable as value, that will be used to format a value in the result table */
    protected $fieldFormatting = [];

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

    /** @var bool if true, each row gets a delete button */
    protected $showDeleteRowButton = false;

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

        return $this->acl->allowed(static::class, Permission::ACCESS_DELETE, [self::TABLE_KEY => $id]);
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

        return $this->acl->allowed(static::class, Permission::ACCESS_EDIT, [self::TABLE_KEY => $id]);
    }

    /**
     * @param $id
     * @param $column
     * @param $checked
     * @return bool
     */
    public function checkCheckbox($id, $column, $checked): bool
    {
        if ( ! $this->canEdit($id)) {
            return false;
        }

        $langCode = $this->getFilters()->getLanguageCode();
        $object   = $this->modelService->getObject($this->getModel(), $id);

        if ($this->relationKeyService->isRelationKey($column)) {
            $this->relationKeyService->set($object, $column, $checked, $langCode);
        } else {
            $object->$column = $checked;
        }

        return $object->save();
    }

    /**
     * @param array $ids
     */
    public function delete(array $ids)
    {
        foreach ($ids as $i => $id) {
            if ( ! $this->canDelete($id)) {
                unset($ids[$i]);
            }
        }

        $objects = $this->modelService->getObjects($this->getModel(), $ids);

        foreach ($objects as $object) {
            $object->delete();
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function formatBoolean($value): string
    {
        return $this->translator->tl('global.' . ($value ? 'yes' : 'no'));
    }

    /**
     * @param $value
     * @param $rowData
     * @param $column
     * @return string
     */
    public function formatCheckbox($value, $rowData, $column)
    {
        $attributes = [
            'type'     => 'checkbox',
            'class'    => 'table-checkbox',
            'data-col' => $column,
        ];

        // we do this check to prevent unused $rowData error
        if (array_key_exists($column, $rowData) && $value) {
            $attributes['checked'] = 'checked';
        } elseif ($value) {
            $attributes['checked'] = 'checked';
        }

        return $this->tag->tagHtml('input', $attributes);
    }

    /**
     * @param $value
     * @return string
     */
    public function formatFinderImage($value): string
    {
        if ( ! $value) {
            return '';
        }

        $thumbUrl = $this->url->get('finderFileThumb', $value);
        $url      = $this->url->get('finderFile', $value);

        $style = 'background-image: url(' . $thumbUrl . ')';

        return $this->tag->tagHtml('div', [
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
     * @return string|null
     */
    public function formatValue(string $column, $value, array $rowData = []): ?string
    {
        if ( ! array_key_exists($column, $this->fieldFormatting)) {
            return null;
        }

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
            return self::TABLE_KEY;
        }

        return $alias . '.' . self::TABLE_KEY;
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
     * @return DataForm|null
     */
    public function getForm(): ?DataForm
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
        $model       = $this->getFilters()->getParentModel();
        $relationKey = $this->getFilters()->getParentRelationKey();

        if ( ! $model || ! $relationKey) {
            return null;
        }

        if ( ! $relation = $this->modelService->getRelation($model, $relationKey)) {
            return null;
        }

        if ($relation->getType() !== Relation::HAS_MANY) {
            return null;
        }

        if ( ! is_string($relation->getReferencedFields())) {
            return null;
        }

        return $relation->getReferencedFields();
    }

    /**
     * @return mixed
     */
    public function getParentRelationValue()
    {
        $model       = $this->getFilters()->getParentModel();
        $editId      = $this->getFilters()->getParentEditId();
        $relationKey = $this->getFilters()->getParentRelationKey();

        if ($editId === 0) {
            return 0;
        }

        $relation     = $this->modelService->getRelation($model, $relationKey);
        $parentObject = $this->modelService->getObject($model, $editId);

        $field = $relation->getFields();

        return $parentObject->$field;
    }

    /**
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
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
        return $this->getParentRelationKey() != null;
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
            $formClass    = $this->getFormClass();
            $editId       = $this->getFilters()->getEditId();
            $languageCode = $this->getFilters()->getWindowLanguageCode();
            $parentEditId = $this->getFilters()->getParentEditId();

            /** @var DataForm $dataForm */
            $dataForm = new $formClass();

            $dataForm->security = $this->securitySingleToken;

            $dataForm
                ->setDataTable($this)
                ->getFilters()
                ->setEditId($editId)
                ->setLanguageCode($languageCode)
                ->setParentEditId($parentEditId);

            $this->form = $dataForm;
        }

        $this->initialize();

        $this->initialized = true;
    }

    /**
     * @inheritdoc
     */
    public function render(): string
    {
        if ( ! $this->acl->dataTableAllowed(static::class)) {
            throw new UnauthorizedException();
        }

        $this->checkValidKey();
        $this->initializeDatatable();
        $this->addAssets();

        return $this->view->getPartial($this->indexView, [
            'tableData'           => $this->getTableData(),
            'jsData'              => $this->getJsData(),
            'editLabel'           => $this->getEditLabel(),
            'deleteLabel'         => $this->getDeleteLabel(),
            'currentLangCode'     => $this->getFilters()->getLanguageCode(),
            'languages'           => $this->languageService->getLanguages(),
            'sortLabel'           => $this->translator->tl('dataTable.sort'),
            'fieldFormatting'     => $this->fieldFormatting,
            'showDeleteRowButton' => $this->showDeleteRowButton && $this->canDelete(),
            'canAdd'              => $this->canAdd(),
            'canEdit'             => $this->canEdit(),
            'canDelete'           => $this->canDelete(),
            'self'                => $this,
        ]);
    }

    /**
     * @return Response
     */
    public function renderAddForm()
    {
        $this->initializeDatatable(true);

        if ($this->getParentRelationKey() && $this->filters->getParentEditId() !== null) {
            $this->form->addHiddenField($this->getParentRelationKey(), $this->getParentRelationValue());
        }

        return $this->form->render();
    }

    /**
     * @return Response
     */
    public function renderEditForm()
    {
        $this->initializeDatatable(true);

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
            'tableData'           => $this->getTableData(),
            'editLabel'           => $this->getEditLabel(),
            'deleteLabel'         => $this->getDeleteLabel(),
            'sortLabel'           => $this->translator->tl('dataTable.sort'),
            'fieldFormatting'     => $this->fieldFormatting,
            'showDeleteRowButton' => $this->showDeleteRowButton && $this->canDelete(),
            'self'                => $this,
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
     * Formats the field as a checkbox, but with the option to add a relationKey if it's stored elsewhere
     *
     * @param string $field
     * @param string|null $relationKey
     */
    public function setCheckboxFormat(string $field, string $relationKey = null)
    {
        $this->setFieldFormatting($field, function ($value, $rowData, $column) use ($relationKey) {
            return $this->formatCheckbox($value, $rowData, $relationKey ?: $column);
        });
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

        if ($this->keyValue->exists($cacheKey)) {
            $newIdsCache = unserialize($this->keyValue->get($cacheKey));
        } else {
            $newIdsCache = (new SubDataTableNewIdsCache)
                ->setModel($this->getModel())
                ->setColumn($this->getParentRelationKey());
        }

        $newIdsCache->addId($editId);

        $this->keyValue->save($cacheKey, serialize($newIdsCache));
    }

    /**
     * @return array
     */
    public function getCachedNewIds(): array
    {
        $cacheKey = $this->getNewIdsCacheKey();

        if ( ! $this->keyValue->exists($cacheKey)) {
            return [];
        }

        return unserialize($this->keyValue->get($cacheKey))->getIds();
    }

    /**
     * Remove cache files
     */
    public function removeNewIdCache()
    {
        $cacheKey = $this->getNewIdsCacheKey();

        $this->keyValue->delete($cacheKey);
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
     * @param bool $blank
     */
    protected function addTableButton(string $icon, string $title, string $class, string $url = null, bool $blank = false)
    {
        $this->tableButtons[] = new TableButton($icon, $title, $class, $url, $blank);
    }

    /**
     * @inheritdoc
     */
    protected function getJsProperties(): array
    {
        return [
            'parentEditId'      => $this->filters->getParentEditId(),
            'parentModel'       => $this->filters->getParentModel(),
            'parentRelationKey' => $this->filters->getParentRelationKey(),
            'labels'            => $this->getLabels(),
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

        $query    = $this->getQuery();
        $limit    = $this->getLimit();
        $page     = $this->getFilters()->getPage();
        $fieldMap = $this->getTableFieldMap();

        $this->tableData = $this->tableDataService->getTableData($query, $page, $limit, $fieldMap);

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
    private function getDeleteLabel(): string
    {
        return ucfirst($this->translator->tl('dataTable.delete.label', ['itemSingular' => $this->getLabels()[0]]));
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
        return $this->getInstance() . self::IDS_CACHE_SUFFIX;
    }

    /**
     * Check if the table used has an id field
     */
    private function checkValidKey()
    {
        $constant = $this->getModel() . '::FIELD_' . strtoupper(self::TABLE_KEY);

        if ( ! defined($constant)) {
            throw new Exception("DataTables only allow tables with a single primary key named '" . self::TABLE_KEY . "'");
        }
    }
}