<?php

namespace KikCMS\Classes\WebForm\DataForm;

use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\StorageData;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\StorageService;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer\Date;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Config\DbConfig;
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
    /** @var array */
    protected $events = [];

    /** @var DataFormFilters */
    protected $filters;

    /** @var string */
    protected $formTemplate = 'dataForm';

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
    public function addDateField(string $key, string $label, array $validators = []): Field
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
     * @return array
     */
    public function getDataStoredElseWhere(int $id, string $langCode = null): array
    {
        $data = [];

        /** @var Field $field */
        foreach ($this->getFieldMap() as $key => $field) {
            if ( ! $field->getStorage()) {
                continue;
            }

            $value = $this->storageService->retrieve($field, $id, $langCode);
            $data[$key] = $field->getFormFormat($value);
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
     * @return Response|string
     */
    public function renderWithData()
    {
        $defaultLangCode = $this->languageService->getDefaultLanguageCode();
        $currentLangCode = $this->getFilters()->getLanguageCode();
        $editId          = $this->getFilters()->getEditId();

        $editData        = $this->getEditData();
        $defaultLangData = $this->getDataStoredElseWhere($editId, $defaultLangCode);
        $defaultLangData = $this->transformDataForDisplay($defaultLangData);

        /** @var Field $field */
        foreach ($this->fieldMap as $key => $field) {
            if (array_key_exists($key, $editData) && $editData[$key] !== null) {
                $field->setDefault($editData[$key]);
            }

            if (array_key_exists($key, $defaultLangData) && $defaultLangData[$key] && $currentLangCode != $defaultLangCode) {
                $field->setPlaceholder($defaultLangData[$key]);
            }
        }

        return $this->render();
    }

    /**
     * @param array $input
     * @return void
     */
    public function successAction(array $input)
    {
        $saveSuccess = $this->saveData($input);

        if ($saveSuccess && ! $this->fieldMap->has(DataTable::EDIT_ID)) {
            $this->addHiddenField(DataTable::EDIT_ID, $this->filters->getEditId());
        }

        if ($saveSuccess) {
            $this->onSave();
            $this->flash->success($this->translator->tl('dataForm.saveSuccess'));
        } else {
            $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            $this->flash->error($this->translator->tl('dataForm.saveFailure'));
        }
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
     * @return DataTable|null
     */
    public function getDataTable()
    {
        return $this->dataTable;
    }

    /**
     * @return array
     */
    public function getEditData(): array
    {
        $editId   = $this->getFilters()->getEditId();
        $langCode = $this->getFilters()->getLanguageCode();

        if ( ! $editId) {
            return [];
        }

        if (isset($this->cachedEditData[$editId])) {
            return $this->cachedEditData[$editId];
        }

        $data = $this->getDataStoredElseWhere($editId, $langCode) + $this->getEditDataForModel();
        $data = $this->transformDataForDisplay($data);

        $this->cachedEditData[$editId] = $data;

        return $data;
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
        if( ! array_key_exists($event, $this->events)){
            $this->events[$event] = [];
        }

        $this->events[$event][] = $callable;
    }

    /**
     * Perform some action on a successful save
     * todo: use event listener, fix usages
     */
    protected function onSave()
    {
    }

    /**
     * @inheritdoc
     * todo: #4 combine with webform, neatify code
     */
    protected function renderDataTableFields()
    {
        parent::renderDataTableFields();

        $parentEditId = 0;

        // if a new id is saved, the field with key editId is set, so we pass it to the subDataTable
        if ($this->fieldMap->has(DataTable::EDIT_ID)) {
            $parentEditId = $this->fieldMap->get(DataTable::EDIT_ID)->getElement()->getValue();
        }

        $languageCode = $this->getFilters()->getLanguageCode();

        /** @var DataTableField $field */
        foreach ($this->getFieldMap() as $key => $field) {
            if ($field->getType() != Field::TYPE_DATA_TABLE) {
                continue;
            }

            $field->getDataTable()->getFilters()->setParentEditId($parentEditId);
            $field->getDataTable()->getFilters()->setLanguageCode($languageCode);

            $renderedDataTable = $field->getDataTable()->render();

            $field->setRenderedDataTable($renderedDataTable);
        }
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

        if ($this->getDataTable() && $this->getDataTable()->isSortable()) {
            $this->setDisplayOrder($storageData);
        }
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

        if (array_key_exists(DataTable::EDIT_ID, $input)) {
            $storageData->setEditId($input[DataTable::EDIT_ID]);
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
        return [WebForm::WEB_FORM_ID, DataTable::EDIT_ID, DataTable::INSTANCE, DataTable::PAGE];
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

        if($success){
            $this->getFilters()->setEditId($storageData->getEditId());
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