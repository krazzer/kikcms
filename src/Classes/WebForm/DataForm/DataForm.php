<?php

namespace KikCMS\Classes\WebForm\DataForm;

use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Services\DataTable\RearrangeService;
use KikCMS\Services\ModelService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCMS\Classes\WebForm\Fields\DateField;
use KikCMS\ObjectLists\FieldMap;
use KikCmsCore\Classes\Model;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Services\WebForm\StorageService;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer\Date;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\WebForm;
use KikCmsCore\Config\DbConfig;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\LanguageService;
use Monolog\Logger;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Resultset;

/**
 * @property DbService $dbService
 * @property LanguageService $languageService
 * @property Logger $logger
 * @property ModelService $modelService
 * @property RelationKeyService $relationKeyService
 * @property StorageService $storageService
 * @property RearrangeService $rearrangeService
 */
abstract class DataForm extends WebForm
{
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

    /** @var Model|null */
    private $object;

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
     * Get the Objects for a DataTableField. This can be useful if you want to validate them
     *
     * @param string $fieldKey
     * @return Model[]|Resultset
     */
    public function getDataTableFieldObjects(string $fieldKey)
    {
        if ( ! $relation = $this->modelService->getRelation($this->getModel(), $fieldKey)) {
            throw new Exception("Relation $fieldKey does not exist");
        }

        if ($object = $this->getObject()) {
            return $object->$fieldKey;
        }

        /** @var DataTableField $field */
        if ( ! $field = $this->getFieldMap()->get($fieldKey)) {
            throw new Exception("Field $fieldKey does not exist");
        }

        if ( ! $ids = $field->getDataTable()->getCachedNewIds()) {
            return [];
        }

        return $this->modelService->getObjects($relation->getReferencedModel(), $ids);
    }

    /**
     * @return mixed|Model|null
     */
    public function getObject(): ?Model
    {
        if ($this->object) {
            return $this->object;
        }

        if ( ! $editId = $this->getFilters()->getEditId()) {
            return null;
        }

        $this->object = $this->modelService->getObject($this->getModel(), $editId);

        return $this->object;
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
    public function initializeForm()
    {
        parent::initializeForm();

        if ( ! $editId = $this->getFilters()->getEditId()) {
            return;
        }

        $defaultLangCode = $this->languageService->getDefaultLanguageCode();
        $currentLangCode = $this->getFilters()->getLanguageCode();

        $editData        = $this->getEditData();
        $defaultLangData = $this->getRelatedData($defaultLangCode);
        $defaultLangData = $this->transformDataForDisplay($defaultLangData);

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
        $editId   = $this->getFilters()->getEditId();
        $langCode = $this->getFilters()->getLanguageCode();

        if ( ! $editId) {
            return [];
        }

        if (isset($this->cachedEditData[$editId])) {
            return $this->cachedEditData[$editId];
        }

        if ( ! $object = $this->getObject()) {
            throw new ObjectNotFoundException(basename($this->getModel()) . ':' . $editId);
        }

        $data = $object->toArray();
        $data = $this->getRelatedData($langCode) + $data;
        $data = $this->transformDataForDisplay((array) $data);

        $this->cachedEditData[$editId] = $data;

        return $data;
    }

    /**
     * What happens after successfully saving the Form's data
     * @param bool $isNew
     */
    public function saveSuccessAction(bool $isNew)
    {
        $this->flash->success($this->translator->tl('dataForm.saveSuccess'));

        if ( ! $isNew) {
            return;
        }

        // re-initialize the form to display the form as if we are editing
        $this->reInitializeForm();
    }

    /**
     * @param array $input
     * @return Response|string
     */
    public function successAction(array $input)
    {
        $isNew = ! (bool) $this->filters->getEditId();

        $saveSuccess = $this->saveData($input);

        if ($saveSuccess) {
            return $this->saveSuccessAction($isNew);
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
     * @param array $input
     * @return StorageData
     */
    protected function getStorageData(array $input): StorageData
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

            // in case of a multicheckbox, and nothing is present, set value as an empty array
            if ( ! array_key_exists($key, $input) && Field::TYPE_MULTI_CHECKBOX) {
                $input[$key] = [];
            }

            if ( ! array_key_exists($key, $input)) {
                continue;
            }

            $value = $this->transformInputForStorage($input, $key);

            $storageData->addFormInputValue($key, $value);
        }

        if ($editId = $this->getFilters()->getEditId()) {
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
     * Perform some action on a successful save
     */
    protected function onSave()
    {
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
     * @inheritdoc
     */
    protected function renderDataTableField(DataTableField $field)
    {
        $langCode     = $this->getFilters()->getLanguageCode();
        $parentEditId = $this->getParentEditIdForDataTableField($field);

        $field->getDataTable()->getFilters()
            ->setParentRelationKey($field->getKey())
            ->setParentModel($this->getModel())
            ->setParentEditId($parentEditId)
            ->setLanguageCode($langCode);

        $field->setRenderedDataTable($field->getDataTable()->render());
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
     * Retrieve the parentEditId for given DataTableField
     * This can be one of the following:
     * - The current objects' id
     * - 0, if their is a relation, but the current forms' object has not been saved yet
     * - null, if there is no relation at all
     *
     * @param DataTableField $field
     * @return int|null
     */
    private function getParentEditIdForDataTableField(DataTableField $field): ?int
    {
        if ($object = $this->getObject()) {
            return (int) $object->id;
        }

        $relation = $this->modelService->getRelation($this->getModel(), $field->getKey());

        return $relation ? 0 : null;
    }

    /**
     * Retrieve data from fields that are not stored in the current DataTable's Table
     *
     * @param null|string $langCode
     * @return array
     */
    private function getRelatedData(string $langCode = null): array
    {
        $data   = [];
        $object = $this->getObject();

        foreach ($this->getFieldMap() as $key => $field) {
            if ($this->relationKeyService->isRelationKey($key)) {
                $data[$key] = $this->relationKeyService->get($object, $key, $langCode);
            }
        }

        return $data;
    }

    /**
     * Get an array of formFields that are used by the system and don't contain user input
     *
     * @return array
     */
    private function getSystemFields()
    {
        return [$this->getFormId(), DataTable::PAGE];
    }

    /**
     * @param array $input
     * @return bool
     */
    private function saveData(array $input): bool
    {
        $storageData = $this->getStorageData($input);

        $model  = $this->getModel();
        $object = $this->getObject() ?: new $model();

        $storageData->setObject($object);

        $this->storageService->setStorageData($storageData);

        $success = $this->storageService->store();

        if ($success) {
            $this->getFilters()->setEditId($storageData->getEditId());
            $this->onSave();
        }

        return $success;
    }

    /**
     * @param StorageData $storageData
     */
    private function setDisplayOrder(StorageData $storageData)
    {
        $dataTable = $this->getDataTable();

        if ($dataTable->isSortableNewFirst()) {
            $storageData->addAdditionalInputValue($dataTable->getSortableField(), 1);
            $this->rearrangeService->makeRoomForFirst($this->getModel());
        } else {
            $newValue = $this->rearrangeService->getMax($this->getModel()) + 1;
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
            if ( ! isset($data[$key]) || ( ! $data[$key] && ! is_array($data[$key]))) {
                continue;
            }

            if ( ! array_key_exists($key, $this->fieldTransformers)) {
                $data[$key] = $field->getFormFormat($data[$key]);
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

    /**
     * Re-initializes the form
     */
    private function reInitializeForm()
    {
        $this->fieldMap = new FieldMap();

        $this->tabs = [];
        $this->keys = [];

        $this->initializeForm();
    }
}