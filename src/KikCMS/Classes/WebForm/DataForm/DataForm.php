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
use Monolog\Logger;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Query\Builder;
use \KikCMS\Classes\WebForm\DataForm\FieldStorage\DataTable as DataTableFieldStorage;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * @property DbService $dbService
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
     * @return array
     */
    public function getDataStoredElseWhere(int $id): array
    {
        $data = [];

        /** @var Field $field */
        foreach ($this->getFields() as $key => $field) {
            if ($this->isStoredElsewhere($field)) {
                $data[$key] = $this->fieldStorage[$field->getKey()]->getValue($id);
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
        $editData = $this->getEditData();

        foreach ($this->fields as $key => &$field) {
            if (array_key_exists($key, $editData)) {
                $field->getElement()->setDefault($editData[$key]);
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
        $editId = $this->filters->getEditId();

        if ( ! $editId) {
            return [];
        }

        if (isset($this->cachedEditData[$editId])) {
            return $this->cachedEditData[$editId];
        }

        $query = new Builder();
        $query
            ->addFrom($this->getModel())
            ->andWhere('id = ' . $editId);

        /** @var Simple $returnData */
        $returnData = $query->getQuery()->execute()->getFirst();

        if ( ! $returnData) {
            return [];
        }

        $data = $returnData->toArray();
        $data = $data + $this->getDataStoredElseWhere($editId);
        $data = $this->transformDataForDisplay($data);

        $this->cachedEditData[$editId] = $data;

        return $data;
    }

    /**
     * @return Filters|DataFormFilters
     */
    public function getEmptyFilters(): Filters
    {
        return new DataFormFilters();
    }

    /**
     * @param array $input
     * @return bool
     */
    private function saveData(array $input): bool
    {
        $insertUpdateData = $this->getInsertUpdateData($input);

        $this->db->begin();

        try {
            if (isset($input[DataTable::EDIT_ID])) {
                $editId = $input[DataTable::EDIT_ID];
                $this->dbService->update($this->getModel(), $insertUpdateData, ['id' => $editId]);
            } else {
                $editId = $this->dbService->insert($this->getModel(), $insertUpdateData);
            }

            $this->filters->setEditId($editId);
            $this->storeFields($input, $editId);
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();

            return false;
        }

        return $this->db->commit();
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
     * Create an array with data from the form that can be inserted or updated directly
     *
     * @param array $input
     * @return array
     */
    private function getInsertUpdateData(array $input): array
    {
        $insertUpdateData = [];

        foreach ($this->fields as $key => $field) {
            if (in_array($key, $this->getSystemFields())) {
                continue;
            }

            // will be saved in another table, so skip here
            if ($this->isStoredElsewhere($field)) {
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
            $value = $this->formatInputValue($value);

            $insertUpdateData[$key] = $value;
        }

        return $insertUpdateData;
    }

    /**
     * Format the forms' input for database insertion
     *
     * @param mixed $value
     * @return mixed|null
     */
    private function formatInputValue($value)
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
     * Store field data that is not stored in de DataTable's main table
     *
     * @param array $input
     * @param $editId
     */
    private function storeFields(array $input, $editId)
    {
        foreach ($this->fields as $key => $field) {
            if ( ! $this->isStoredElsewhere($field)) {
                continue;
            }

            if ( ! array_key_exists($key, $input)) {
                continue;
            }

            $value = $this->transformInputForStorage($input, $key);
            $value = $this->formatInputValue($value);

            //todo: pass languageCode
            $this->fieldStorage[$key]->store($value, $editId);
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
        foreach ($this->fields as $key => $field) {
            if ( ! array_key_exists($key, $this->fieldTransformers) || ! $data[$key]) {
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