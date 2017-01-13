<?php

namespace KikCMS\Classes\WebForm\DataForm;

use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Config\StatusCodes;
use Monolog\Logger;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property Logger $logger
 */
class DataForm extends WebForm
{
    /** @var string */
    protected $table;

    /** @var string */
    protected $tableKey = 'id';

    /** @var string */
    protected $formTemplate = 'dataForm';

    /** @var FieldStorage[] */
    protected $fieldStorage;

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        parent::__construct();

        $this->table = $table;
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
     * @param array $input
     * @return void
     */
    public function successAction(array $input)
    {
        $editId = $this->saveData($input);

        if($editId && !array_key_exists(DataTable::EDIT_ID, $input)){
            $this->addHiddenField(DataTable::EDIT_ID, $editId);
        }

        if ($editId) {
            $this->flash->success($this->translator->tl('dataForm.saveSuccess'));
        } else {
            $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            $this->flash->error($this->translator->tl('dataForm.saveFailure'));
        }
    }

    /**
     * @param int $editId
     * @return Response|string
     */
    public function renderWithData(int $editId)
    {
        $editData = $this->getEditData($editId);

        foreach ($this->fields as $key => &$field) {
            if (array_key_exists($key, $editData)) {
                $field->getElement()->setDefault($editData[$key]);
            }
        }

        return $this->render();
    }

    /**
     * @param FieldStorage $fieldStorage
     */
    public function addFieldStorage(FieldStorage $fieldStorage)
    {
        $this->fieldStorage[$fieldStorage->getField()->getKey()] = $fieldStorage;
    }

    /**
     * Retrieve data from fields that are not stored in the current DataTable's Table
     *
     * @param $id
     * @return array
     */
    public function getDataStoredElseWhere($id): array
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
     * @param $field
     *
     * @return bool
     */
    public function isStoredElsewhere(Field $field): bool
    {
        return array_key_exists($field->getKey(), $this->fieldStorage);
    }

    /**
     * @param int $id
     * @return array
     */
    private function getEditData(int $id)
    {
        $query = new Builder();
        $query
            ->addFrom($this->table)
            ->andWhere('id = ' . $id);

        $data = $query->getQuery()->execute()->getFirst()->toArray();
        $data += $this->getDataStoredElseWhere($id);

        return $data;
    }

    /**
     * @param array $input
     * @return mixed
     */
    private function saveData(array $input)
    {
        $insertUpdateData = $this->getInsertUpdateData($input);

        /** @var Model $model */
        $model = new $this->table();
        $table = $model->getSource();

        $this->db->begin();

        try {
            if (isset($input[DataTable::EDIT_ID])) {
                $editId = $input[DataTable::EDIT_ID];
                $this->dbService->update($table, $insertUpdateData, [$this->tableKey => $editId]);
            } else {
                $editId = $this->dbService->insert($table, $insertUpdateData);
            }

            $this->storeFields($input, $editId);
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();

            return false;
        }

        $this->db->commit();

        return $editId;
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

            $insertUpdateData[$key] = $this->formatInputValue($input[$key]);
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
     * @param array $input
     * @param $editId
     */
    private function storeFields(array $input, $editId)
    {
        foreach ($this->fields as $key => $field) {
            if ( ! $this->isStoredElsewhere($field)) {
                continue;
            }

            $this->fieldStorage[$key]->store($input, $editId);
        }
    }
}