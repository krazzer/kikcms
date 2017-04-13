<?php

namespace KikCMS\Classes\WebForm\DataForm;

use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer\Date;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Config\StatusCodes;
use KikCMS\Services\LanguageService;
use Monolog\Logger;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use \KikCMS\Classes\WebForm\DataForm\FieldStorage\DataTable as DataTableFieldStorage;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * @property DbService $dbService
 * @property LanguageService $languageService
 * @property Logger $logger
 */
abstract class DataForm extends WebForm
{
    /** @var DataFormFilters */
    protected $filters;

    /** @var string */
    protected $formTemplate = 'dataForm';

    /** @var FieldStorage[] */
    protected $fieldStorage = [];

    /** @var FieldTransformer[] */
    protected $fieldTransformers = [];

    /** @var array local cache for edit data */
    private $cachedEditData = [];

    /**
     * @return string
     */
    public abstract function getModel(): string;

    /**
     * @param FieldStorage $fieldStorage
     */
    public function addFieldStorage(FieldStorage $fieldStorage)
    {
        $this->fieldStorage[$fieldStorage->getField()->getKey()] = $fieldStorage;
    }

    /**
     * @param FieldTransformer $fieldTransformer
     */
    public function addFieldTransformer(FieldTransformer $fieldTransformer)
    {
        $this->fieldTransformers[$fieldTransformer->getField()->getKey()] = $fieldTransformer;
    }

    /**
     * @param DataTable $dataTable
     * @param string $label
     *
     * @return Field|DataTableField
     */
    public function addDataTableField(DataTable $dataTable, string $label)
    {
        $dataTableElement = new Hidden('dt');
        $dataTableElement->setLabel($label);
        $dataTableElement->setDefault($dataTable->getInstance());

        $dataTableField = $this->addField(new DataTableField($dataTableElement, $dataTable));

        $dataTableFieldStorage = new DataTableFieldStorage();
        $dataTableFieldStorage->setField($dataTableField);

        $this->addFieldStorage($dataTableFieldStorage);

        return $dataTableField;
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
     * @param null $languageCode
     * @return array
     */
    public function getDataStoredElseWhere(int $id, $languageCode = null): array
    {
        $data = [];

        /** @var Field $field */
        foreach ($this->getFields() as $key => $field) {
            if ($this->isStoredElsewhere($field)) {
                $data[$key] = $this->fieldStorage[$field->getKey()]->getValue($id, $languageCode);
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
     * @param $field
     *
     * @return bool
     */
    public function isStoredElsewhere(Field $field): bool
    {
        return array_key_exists($field->getKey(), $this->fieldStorage);
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

        foreach ($this->fields as $key => &$field) {
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

        if ($saveSuccess && ! array_key_exists(DataTable::EDIT_ID, $input)) {
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
     * @return array
     */
    public function getEditData(): array
    {
        $editId       = $this->getFilters()->getEditId();
        $languageCode = $this->getFilters()->getLanguageCode();

        if ( ! $editId) {
            return [];
        }

        if (isset($this->cachedEditData[$editId])) {
            return $this->cachedEditData[$editId];
        }

        /** @var Simple $returnData */
        $returnData = $this->getEditDataQuery()->getQuery()->execute()->getFirst();

        if ( ! $returnData) {
            return [];
        }

        $data = $this->getDataStoredElseWhere($editId, $languageCode) + $returnData->toArray();
        $data = $this->transformDataForDisplay($data);

        $this->cachedEditData[$editId] = $data;

        return $data;
    }

    /**
     * @return Builder
     */
    protected function getEditDataQuery()
    {
        $editId = $this->getFilters()->getEditId();

        $query = (new Builder())
            ->addFrom($this->getModel())
            ->andWhere('id = ' . $editId);

        return $query;
    }

    /**
     * Format the forms' input for database insertion
     *
     * @param mixed $value
     * @return mixed|null
     */
    private function formatInputValueForStorage($value)
    {
        // convert empty string to null
        if ($value === '') {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return $value;
    }

    /**
     * @return Filters|DataFormFilters
     */
    public function getEmptyFilters(): Filters
    {
        return new DataFormFilters();
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
     * Perform some action on a successful save
     */
    protected function onSave()
    {
    }

    /**
     * @param array $input
     * @return StorageData
     */
    private function getStorageData(array $input): StorageData
    {
        $storageData = new StorageData();

        foreach ($this->fields as $key => $field) {
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
            $value = $this->formatInputValueForStorage($value);

            $storageData->addValue($key, $value, $this->isStoredElsewhere($field));
        }

        return $storageData;
    }

    /**
     * @param array $input
     * @return bool
     */
    private function saveData(array $input): bool
    {
        $storageData = $this->getStorageData($input);

        $this->db->begin();

        try {
            if (isset($input[DataTable::EDIT_ID])) {
                $editId = $input[DataTable::EDIT_ID];
                $this->dbService->update($this->getModel(), $storageData->getDataStoredInTable(), ['id' => $editId]);
            } else {
                // if a temporary key is inserted, fk checks needs to be disabled for insert
                if ($this->getFilters()->getParentEditId() === 0) {
                    $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
                }
                $editId = $this->dbService->insert($this->getModel(), $storageData->getDataStoredInTable());
                if ($this->getFilters()->getParentEditId() === 0) {
                    $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
                }
            }

            $this->filters->setEditId($editId);

            foreach ($storageData->getDataStoredElseWhere() as $key => $value) {
                $this->fieldStorage[$key]->store($value, $editId, $this->getFilters()->getLanguageCode());
            }
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();

            return false;
        }

        return $this->db->commit();
    }

    /**
     * Update the data with any autocomplete field value
     *
     * @param array $data
     * @return array
     */
    private function transformDataForDisplay(array $data): array
    {
        foreach ($this->fields as $key => $field) {
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