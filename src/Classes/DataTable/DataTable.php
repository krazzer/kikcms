<?php declare(strict_types=1);

namespace KikCMS\Classes\DataTable;


use Exception;
use KikCMS\Config\FinderConfig;
use KikCMS\Services\TwigService;
use KikCMS\Services\WebForm\DataFormService;
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
use KikCMS\Services\DataTable\DataTableFilterService;
use KikCMS\Services\LanguageService;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Validation\Validator;

/**
 * @property AccessControl $acl
 * @property DbService $dbService
 * @property DataTableFilterService $dataTableFilterService
 * @property DataFormService $dataFormService
 * @property KeyValue $keyValue
 * @property LanguageService $languageService
 * @property QueryService $queryService
 * @property ModelService $modelService
 * @property RelationKeyService $relationKeyService
 * @property SecuritySingleToken $securitySingleToken
 * @property TableDataService $tableDataService
 * @property Translator $translator
 * @property TwigService $twigService
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
        'dataTable.restoreConfirm',
    ];

    /** @var DataForm */
    protected $form;

    /** @var DataTableFilters */
    protected $filters;

    /** @var Filter[] */
    protected $customFilters = [];

    /** @var false|string if set, the datatable will let you select or upload a file directly, using the set field */
    protected $directImageField = false;

    /** @var Validator[] */
    protected $directImageValidators = [];

    /** @var array default field values to be set for the child object */
    protected $directImageDefaults = [];

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

    /** @var int amount of rows shown on one page */
    protected $limit = 100;

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

    /**
     * Tracks whether the function 'initializeDatatable' has been run yet
     * @var bool
     */
    private $initialized;

    /**
     * @return string
     */
    public abstract function getModel(): string;

    /**
     * @return string
     */
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
        $object   = $this->modelService->getObject($this->getModel(), (int) $id);

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

        $thumbUrl = $this->twigService->mediaFile($value, FinderConfig::DEFAULT_THUMB_TYPE, true);
        $url      = $this->url->get('file', $value);

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
    public function getParentRelationField(): ?string
    {
        return $this->dataTableFilterService->getParentRelationField($this->getFilters());
    }

    /**
     * @return mixed
     */
    public function getParentRelationValue()
    {
        return $this->dataTableFilterService->getParentRelationValue($this->getFilters());
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

        if($this->isSortable()){
            $this->rearrangeService->checkOrderIntegrity($this->getModel(), $this->getSortableField());
        }

        return $this->view->getPartial($this->indexView, [
            'tableData'           => $this->getTableData(),
            'jsData'              => $this->getJsData(),
            'editLabel'           => $this->getEditLabel(),
            'deleteLabel'         => $this->getDeleteLabel(),
            'currentLangCode'     => $this->getFilters()->getLanguageCode(),
            'languages'           => $this->languageService->getLanguages(),
            'sortLabel'           => $this->translator->tl('dataTable.sort'),
            'fieldFormatting'     => $this->fieldFormatting,
            'directImageField'    => $this->directImageField,
            'showDeleteRowButton' => $this->showDeleteRowButton && $this->canDelete(),
            'canAdd'              => $this->canAdd(),
            'canEdit'             => $this->canEdit(),
            'canDelete'           => $this->canDelete(),
            'self'                => $this,
        ]);
    }

    /**
     * @return string
     */
    public function renderAddForm(): string
    {
        $this->initializeDatatable(true);

        if ($this->getParentRelationField() && $this->filters->getParentEditId() !== null) {
            $this->form->addHiddenField($this->getParentRelationField(), $this->getParentRelationValue());
        }

        return $this->form->render();
    }

    /**
     * @return string
     */
    public function renderEditForm(): string
    {
        if( ! $this->getFormClass()){
            return '';
        }

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
    final public function getQuery(): Builder
    {
        $query           = $this->getDefaultQuery();
        $filters         = $this->getFilters();
        $isSortable      = $this->isSortable();
        $sortableField   = $this->getSortableField();
        $cachedNewIds    = $this->getCachedNewIds();
        $aliasedTableKey = $this->getAliasedTableKey();
        $customFilters   = $this->getCustomFilters();
        $searchFields    = $this->getSearchableFields();

        if ($alias = $this->getAlias()) {
            $sortableField = $alias . '.' . $sortableField;
        }

        $this->dataTableFilterService->addSearchFilter($query, $filters, $searchFields);
        $this->dataTableFilterService->addSortFilter($query, $filters, $isSortable, $sortableField);
        $this->dataTableFilterService->addSubDataTableFilter($query, $filters, $cachedNewIds, $aliasedTableKey);
        $this->dataTableFilterService->addCustomFilters($query, $filters, $customFilters);

        return $query;
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
                ->setColumn($this->getParentRelationField());
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
     * @param string $renderedForm
     * @return string
     */
    public function renderWindow(string $renderedForm): string
    {
        if( ! $this->getFormClass()){
            return '';
        }

        $template   = $this->getFilters()->getEditId() ? 'edit' : 'add';
        $objectName = $this->dataFormService->getObjectName($this->form);

        return $this->renderView($template, [
            'tabs'            => $this->form->getTabs(),
            'currentTab'      => $this->form->getCurrentTab(),
            'multiLingual'    => $this->isMultiLingual(),
            'labels'          => $this->getLabels(),
            'currentLangCode' => $this->getFilters()->getWindowLanguageCode(),
            'editData'        => $this->getForm()->getEditData(),
            'canEdit'         => $this->canEdit($this->form->getFilters()->getEditId()),
            'languages'       => $this->languageService->getLanguages(),
            'form'            => $renderedForm,
            'objectName'      => $objectName,
        ]);
    }

    /**
     * @param string $icon
     * @param string $title
     * @param string $class
     * @param string|null $url
     * @param bool $blank
     * @param string|null $warning
     */
    protected function addTableButton(string $icon, string $title, string $class, string $url = null, bool $blank = false, string $warning = null)
    {
        $this->tableButtons[] = new TableButton($icon, $title, $class, $url, $blank, $warning);
    }

    /**
     * @return false|string
     */
    public function getDirectImageField()
    {
        return $this->directImageField;
    }

    /**
     * @return array
     */
    public function getDirectImageDefaults(): array
    {
        return $this->directImageDefaults;
    }

    /**
     * @return Validator[]
     */
    public function getDirectImageValidators(): array
    {
        return $this->directImageValidators;
    }

    /**
     * @param bool $sortableNewFirst
     * @return DataTable
     */
    public function setSortableNewFirst(bool $sortableNewFirst): DataTable
    {
        $this->sortableNewFirst = $sortableNewFirst;
        return $this;
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