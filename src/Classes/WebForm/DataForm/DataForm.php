<?php

namespace KikCMS\Classes\WebForm\DataForm;

use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\None;
use KikCMS\Classes\WebForm\Fields\DateField;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Exceptions\ParentRelationKeyReferenceMissingException;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\StorageData;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\StorageService;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer\Date;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\WebForm;
use KikCmsCore\Config\DbConfig;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\LanguageService;
use Monolog\Logger;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property LanguageService $languageService
 * @property Logger $logger
 * @property StorageService $storageService
 */
abstract class DataForm extends WebForm
{
    const EDIT_ID = 'editId';

    /** @var array */
    protected $events = [];

    /** @var DataFormFilters */
    protected $filters;

    /** @var FieldTransformer[] */
    protected $fieldTransformers = [];

    /** @var string */
    protected $createdAtField = 'created_at';

    /** @var string */
    protected $updatedAtField = 'updated_at';

    /** @var bool */
    protected $saveCreatedAt = false;

    /** @var bool */
    protected $saveUpdatedAt = false;

    /** @var bool */
    protected $displaySendButton = false;

    /** @var array local cache for edit data */
    private $cachedEditData = [];

    /** @var DataTable, will be automatically set when this form is initialized by a DataTable */
    private $dataTable;

    /**
     * @return string
     */
    public abstract function getModel(): string;

    /**
     * @param FieldTransformer $fieldTransformer
     */
    public function addFieldTransformer(FieldTransformer $fieldTransformer)
    {
        $this->fieldTransformers[$fieldTransformer->getField()->getKey()] = $fieldTransformer;
    }

    /**
     * @inheritdoc
     */
    public function addDateField(string $key, string $label, array $validators = []): DateField
    {
        $dateField = parent::addDateField($key, $label, $validators);

        $this->addFieldTransformer(new Date($dateField));

        return $dateField;
    }

    /**
     * Retrieve data from fields that are not stored in the current DataTable's Table
     *
     * @param int $id
     * @param null|string $langCode
     * @param array $tableData
     * @return array
     */
    public function getDataStoredElseWhere(int $id, string $langCode = null, array $tableData): array
    {
        $data = [];

        foreach ($this->getFieldMap() as $key => $field) {
            if ($field->getStorage() && ! $field->getStorage() instanceOf None) {
                $value      = $this->storageService->retrieve($field, $id, $langCode, $tableData);
                $data[$key] = $field->getFormFormat($value);
            }
        }

        return $data;
    }

    /**
     * @return DataFormFilters|Filters
     */
    public function getFilters(): Filters
    {
        return parent::getFilters();
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if ( ! $editId = $this->getFilters()->getEditId()) {
            return parent::render();
        }

        $defaultLangCode = $this->languageService->getDefaultLanguageCode();
        $currentLangCode = $this->getFilters()->getLanguageCode();

        $this->initializeForm();

        $editData        = $this->getEditData();
        $defaultLangData = $this->getDataStoredElseWhere($editId, $defaultLangCode, $editData);
        $defaultLangData = $this->transformDataForDisplay($defaultLangData);

        $this->addHiddenField(DataForm::EDIT_ID, $editId);

        /** @var Field $field */
        foreach ($this->fieldMap as $key => $field) {
            if (array_key_exists($key, $editData) && $editData[$key] !== null) {
                $field->setDefault($editData[$key]);
            }

            if (array_key_exists($key, $defaultLangData) && $defaultLangData[$key] && $currentLangCode != $defaultLangCode) {
                if (is_string($defaultLangData[$key])) {
                    $field->setPlaceholder($defaultLangData[$key]);
                }
            }
        }

        return parent::render();
    }

    /**
     * @return null|DataTable
     */
    public function getDataTable(): ?DataTable
    {
        return $this->dataTable;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getEditData(): array
    {
        if ( ! $this->initialized) {
            throw new Exception('DataForm::getEditData cannot be called if the form is not initialized');
        }

        $editId   = $this->getFilters()->getEditId();
        $langCode = $this->getFilters()->getLanguageCode();

        if ( ! $editId) {
            return [];
        }

        if (isset($this->cachedEditData[$editId])) {
            return $this->cachedEditData[$editId];
        }

        $data = $this->getEditDataForModel();
        $data = $this->getDataStoredElseWhere($editId, $langCode, $data) + $data;
        $data = $this->transformDataForDisplay((array) $data);

        $this->cachedEditData[$editId] = $data;

        return $data;
    }

    /**
     * What happens after successfully saving the Form's data
     */
    public function saveSuccessAction()
    {
        $this->flash->success($this->translator->tl('dataForm.saveSuccess'));
    }

    /**
     * @param array $input
     * @return Response|string
     */
    public function successAction(array $input)
    {
        $saveSuccess = $this->saveData($input);

        if ($saveSuccess) {
            return $this->saveSuccessAction();
        } else {
            $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            $this->flash->error($this->translator->tl('dataForm.saveFailure'));
        }

        return null;
    }

    /**
     * @param array $input
     * @return ErrorContainer
     */
    public function validate(array $input): ErrorContainer
    {
        return new ErrorContainer();
    }

    /**
     * @param DataTable $dataTable
     * @return $this|DataForm
     */
    public function setDataTable(DataTable $dataTable): DataForm
    {
        $this->dataTable = $dataTable;
        return $this;
    }

    /**
     * @return array
     */
    protected function getEditDataForModel(): array
    {
        $editId = $this->getFilters()->getEditId();

        $query = (new Builder())
            ->addFrom($this->getModel())
            ->andWhere('id = ' . $editId);

        return $this->dbService->getRow($query);
    }

    /**
     * @return Filters|DataFormFilters
     */
    public function getEmptyFilters(): Filters
    {
        return new DataFormFilters();
    }

    /**
     * @param string $event
     * @param callable $callable
     */
    protected function addEventListener(string $event, callable $callable)
    {
        if ( ! array_key_exists($event, $this->events)) {
            $this->events[$event] = [];
        }

        $this->events[$event][] = $callable;
    }

    /**
     * Perform some action on a successful save
     */
    protected function onSave()
    {
    }

    /**
     * @param DataTableField $field
     * @throws ParentRelationKeyReferenceMissingException
     */
    protected function renderDataTableField(DataTableField $field)
    {
        $langCode     = $this->getFilters()->getLanguageCode();
        $parentEditId = $this->getParentEditIdForField($field);

        $field->getDataTable()->getFilters()->setParentEditId($parentEditId);
        $field->getDataTable()->getFilters()->setLanguageCode($langCode);

        $field->setRenderedDataTable($field->getDataTable()->render());
    }

    /**
     * Pre-fetch editData, so for loops through all fields do not conflict
     *
     * @inheritdoc
     */
    protected function renderDataTableFields()
    {
        $this->getEditData();

        parent::renderDataTableFields();
    }

    /**
     * @param StorageData $storageData
     */
    private function addAutoGeneratedInput(StorageData $storageData)
    {
        if ($this->saveCreatedAt && ! $this->getFilters()->getEditId()) {
            $storageData->addAdditionalInputValue($this->createdAtField, (new \DateTime())->format(DbConfig::SQL_DATETIME_FORMAT));
        }

        if ($this->saveUpdatedAt && $this->getFilters()->getEditId()) {
            $storageData->addAdditionalInputValue($this->updatedAtField, (new \DateTime())->format(DbConfig::SQL_DATETIME_FORMAT));
        }

        if ($this->getDataTable() && $this->getDataTable()->isSortable() && ! $this->getFilters()->getEditId()) {
            $this->setDisplayOrder($storageData);
        }
    }

    /**
     * @param DataTableField $field
     * @return int|null
     */
    private function getParentEditIdForField(DataTableField $field): ?int
    {
        if( ! $editId = $this->getFilters()->getEditId()){
            return $field->getDataTable()->hasParent() ? 0 : null;
        }

        return $this->storageService->getRelatedValueForField($field, $this->getEditData(), $editId);
    }

    /**
     * @param array $input
     * @return StorageData
     */
    private function getStorageData(array $input): StorageData
    {
        $storageData = new StorageData();

        /** @var Field $field */
        foreach ($this->fieldMap as $key => $field) {
            if (in_array($key, $this->getSystemFields())) {
                continue;
            }

            // in case of a checkbox, set the value by its existence
            if ($field->getType() == Field::TYPE_CHECKBOX) {
                $input[$key] = isset($input[$key]) ? 1 : 0;
            }

            if ( ! array_key_exists($key, $input)) {
                continue;
            }

            $value = $this->transformInputForStorage($input, $key);

            $storageData->addFormInputValue($key, $value);
        }

        // check if the form was saved before, or that the editId was already set
        if (array_key_exists(self::EDIT_ID, $input)) {
            $storageData->setEditId($input[self::EDIT_ID]);
        } elseif($editId = $this->getFilters()->getEditId()){
            $storageData->setEditId($editId);
        }

        $storageData->setLanguageCode($this->getFilters()->getLanguageCode());
        $storageData->setParentEditId($this->getFilters()->getParentEditId());
        $storageData->setTable($this->getModel());
        $storageData->setFieldMap($this->fieldMap);
        $storageData->setEvents($this->events);

        $this->addAutoGeneratedInput($storageData);

        return $storageData;
    }

    /**
     * Get an array of formFields that are used by the system and don't contain user input
     *
     * @return array
     */
    private function getSystemFields()
    {
        return [self::WEB_FORM_ID, self::EDIT_ID, DataTable::INSTANCE, DataTable::PAGE];
    }

    /**
     * @param array $input
     * @return bool
     */
    private function saveData(array $input): bool
    {
        $storageData = $this->getStorageData($input);

        $this->storageService->setStorageData($storageData);

        $success = $this->storageService->store();

        if ($success) {
            $this->getFilters()->setEditId($storageData->getEditId());

            if ( ! $this->fieldMap->has(self::EDIT_ID)) {
                $this->addHiddenField(self::EDIT_ID, $this->getFilters()->getEditId());
            }

            $this->onSave();
        }

        return $success;
    }

    /**
     * @param StorageData $storageData
     */
    private function setDisplayOrder(StorageData $storageData)
    {
        $dataTable  = $this->getDataTable();
        $rearranger = $this->getDataTable()->getRearranger();

        if ($dataTable->isSortableNewFirst()) {
            $storageData->addAdditionalInputValue($dataTable->getSortableField(), 1);
            $rearranger->makeRoomForFirst();
        } else {
            $newValue = $rearranger->getMax() + 1;
            $storageData->addAdditionalInputValue($dataTable->getSortableField(), $newValue);
        }
    }

    /**
     * Update the data with any autocomplete field value
     *
     * @param array $data
     * @return array
     */
    private function transformDataForDisplay(array $data): array
    {
        foreach ($this->fieldMap as $key => $field) {
            if ( ! array_key_exists($key, $this->fieldTransformers) || ! isset($data[$key]) || ! $data[$key]) {
                continue;
            }

            $data[$key] = $this->fieldTransformers[$key]->toDisplay($data[$key]);
        }

        return $data;
    }

    /**
     * @param array $input
     * @param string $key
     *
     * @return mixed
     */
    private function transformInputForStorage(array $input, string $key)
    {
        $value = $input[$key];

        if ( ! $value || ! array_key_exists($key, $this->fieldTransformers)) {
            return $value;
        }

        return $this->fieldTransformers[$key]->toStorage($value);
    }
}